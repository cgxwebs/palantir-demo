<?php

namespace App\Controller;

use App\AppService\FacebookDeletionDecoder;
use App\Entity\DataDeletionLog;
use App\Repository\ChatroomRepository;
use App\Repository\DataDeletionLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HomeController extends AbstractController
{
    public function __construct(private ChatroomRepository $chatroomRepository, private EntityManagerInterface $entityManager, private FacebookDeletionDecoder $facebookDeletionDecoder)
    {
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route('/privacy-policy', name: 'privacy_policy')]
    public function privacy_policy(): Response
    {
        // As required by Facebook
        return $this->render('privacy_policy.html.twig');
    }

    #[Route('/delete-data', name: 'delete_data')]
    public function delete_data(Request $request): Response
    {
        $signed_request = $request->request->get('signed_request');
        if (empty($signed_request)) {
            throw $this->createNotFoundException();
        }

        try {
            $signed_request = $this->facebookDeletionDecoder->parse_signed_request($signed_request);
            $facebook_id = $signed_request['user_id'];
        } catch(\Exception) {
            throw $this->createNotFoundException();
        }

        // As required by Facebook
        if (!empty($facebook_id) && strlen($facebook_id) >= 12) {
            $affected = $this->chatroomRepository->removeChatroomByFacebookId($facebook_id);
            if ($affected > 0) {
                $log = new DataDeletionLog();
                $log->setMeta([
                    'deleted_rows' => $affected
                ]);
                $log->setConfirmationId(md5(uniqid()));
                $this->entityManager->persist($log);
                $this->entityManager->flush();
                return $this->json([
                    'url' => $this->generateUrl('delete_confirmation', [
                        'confirmation_id' => $log->getConfirmationId()
                    ], UrlGeneratorInterface::ABSOLUTE_URL),
                    'confirmation_code' => $log->getConfirmationId()
                ]);
            }
        }

        throw $this->createNotFoundException();
    }

    #[Route('/delete-confirmation/{confirmation_id}', name: 'delete_confirmation')]
    public function delete_confirmation(DataDeletionLog $log): Response
    {
        $count = $log->getMeta()['deleted_rows'];
        return new Response('Data Deletion Status: Completed<br/> Affected Chatrooms: ' . $count);
    }

}
