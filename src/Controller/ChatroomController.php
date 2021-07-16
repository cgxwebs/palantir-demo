<?php

namespace App\Controller;

use App\AppService\ChatMessageHandler;
use App\AppService\ChatMessagePurger;
use App\AppService\FacebookAuthenticator;
use App\AppService\QRHandler;
use App\Entity\Chatroom;
use App\Entity\Participant;
use App\Repository\ChatMessageRepository;
use App\DomainService\ChatroomManager;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\QrCode\QrCodeGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/chatroom')]
class ChatroomController extends AbstractController
{
    public function __construct(private ChatroomManager $chatroomManager, private EntityManagerInterface $entityManager, private QrCodeGenerator $qrCodeGenerator,  private ChatMessageRepository $chatMessageRepository, private FacebookAuthenticator $facebookAuthenticator, private ChatMessageHandler $chatMessageHandler, private QRHandler $QRHandler)
    {
    }

    #[Route('/create/{name?}', name: 'create_chatroom', methods: ['GET'])]
    public function create(Chatroom $chatroom = null): Response
    {
        $qrCodes = [];
        if (!is_null($chatroom)) {
            foreach ($chatroom->getParticipants() as $participant) {
                $qrCodes[] = [
                    'name' => $participant->getName(),
                    'key_hash' => $participant->getKeyHash(),
                    'image' => base64_encode($this->qrCodeGenerator->getTotpQrCode($participant)->writeString()),
                ];
            }
        }

        return $this->render('chatroom/create.html.twig', [
            'qr_codes' => $qrCodes,
            'has_chatroom' => !is_null($chatroom),
            'chatroom' => $chatroom,
        ]);
    }

    #[Route('/create_redirect', name: 'create_post_chatroom')]
    public function createRoom()
    {
        $login_url = $this->facebookAuthenticator->attemptAuthentication();
        if (!empty($login_url)) {
            return $this->redirect($login_url);
        }

        $user_info = $this->facebookAuthenticator->getUserInfo();
        if (empty($user_info['name'])) {
            $this->addFlash('error', 'Cannot authenticate with Facebook.');
            return $this->redirectToRoute('create_chatroom');
        }

        $chatroom = $this->chatroomManager->createRoom(days_valid: 7, creator_info: $user_info);
        $this->entityManager->persist($chatroom);
        $this->entityManager->flush();

        return $this->redirectToRoute('create_chatroom', ['name' => $chatroom->getName()]);
    }

    #[Route('/pair/{key_hash}', name: 'pair_chatroom')]
    public function pair(Participant $participant): Response
    {
        if (empty($participant->getRoomKey())) {
            throw $this->createNotFoundException();
        }

        $qr_code = [
            'image' => base64_encode($this->qrCodeGenerator->getTotpQrCode($participant)->writeString()),
        ];

        return $this->render('chatroom/pair.html.twig', [
            'qr_code' => $qr_code,
            'participant' => $participant,
            'chatroom' => $participant->getChatroom(),
            'key_hash' => $participant->getKeyHash(),
        ]);
    }

    #[Route('/download-roomkey/{key_hash}', name: 'download_roomkey_chatroom')]
    public function downloadRoomKey(Participant $participant): Response
    {
        if (empty($participant->getRoomKey())) {
            throw $this->createNotFoundException();
        }

        $roomkey_qr_code = $this->QRHandler->createRoomKeyQR($participant);
        $random_filename = 'ROOMKEY_' . md5(uniqid()) . '.png';

        return new Response($roomkey_qr_code->writeString(), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename=' . $random_filename
        ]);
    }

    #[IsGranted('ROLE_CHAT_PARTICIPANT')]
    #[Route('/lobby/{name}', name: 'lobby_chatroom', methods: ['GET'])]
    public function lobby(Chatroom $chatroom, Request $request): Response
    {
        /**
         * @var Participant $user ;
         */
        $user = $this->getUser();
        $user->rebuildRoomKey($request);

        if ($chatroom !== $user->getChatroom()) {
            return $this->redirectToRoute('app_logout');
        }

        $today = new \DateTime();
        if ($today > $chatroom->getExpiresOn()) {
            $this->entityManager->remove($chatroom);
            $this->entityManager->flush();
            return $this->redirectToRoute('app_logout');
        }

        return $this->render('chatroom/lobby.html.twig', [
            'decoded_messages' => [],
            'user' => $user
        ]);
    }

    #[IsGranted('ROLE_CHAT_PARTICIPANT')]
    #[Route('/view-messages/{name}', name: 'lobby_messages_chatroom', methods: ['GET'])]
    public function viewMessages(Chatroom $chatroom, Request $request, ChatMessagePurger $chatMessagePurger): Response
    {
        /**
         * @var Participant $user ;
         */
        $user = $this->getUser();
        $user->rebuildRoomKey($request);

        if ($chatroom !== $user->getChatroom()) {
            return $this->redirectToRoute('app_logout');
        }

        $messages = $this->chatMessageRepository->findUnreadMessages($user);
        $decoded_messages = $this->chatMessageHandler->decodeMessages($user, $messages);

        $chatMessagePurger->markMessagesAsRead($user, $messages);

        return $this->render('chatroom/lobby_messages.html.twig', [
            'decoded_messages' => $decoded_messages,
            'user' => $user
        ]);
    }

    #[Route('/lobby/{name}', name: 'send_message_chatroom', methods: ['POST'])]
    public function sendMessage(Chatroom $chatroom, Request $request): Response
    {
        /**
         * @var Participant $user ;
         */
        $user = $this->getUser();
        $user->rebuildRoomKey($request);

        if ($chatroom !== $user->getChatroom()) {
            return $this->redirectToRoute('app_logout');
        }

        $submit = $request->request->get('submit');
        $csrf_valid = $this->isCsrfTokenValid('new_message', $request->request->get('csrf_token'));

        if ($csrf_valid && $submit === $user->getKeyHash()) {
            $this->chatMessageHandler->sendMessage($user, $request->request->get('new_message'));
            $this->addFlash('success', 'Message sent and will be available shortly.');
        }

        return $this->redirectToRoute('lobby_chatroom', ['name' => $request->attributes->get('name')]);
    }
}
