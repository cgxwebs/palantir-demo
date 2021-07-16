<?php

namespace App\MessageHandler;

use App\Message\UnencryptedChatMessage;
use App\Repository\ParticipantRepository;
use App\DomainService\MessageEncoder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class UnencryptedChatMessageHandler implements MessageHandlerInterface
{
    public function __construct(private EntityManagerInterface $entityManager, private MessageEncoder $messageEncoder, private ParticipantRepository $participantRepository)
    {
    }

    public function __invoke(UnencryptedChatMessage $message)
    {
        $room_key = $message->getRoomKey();
        $participant = $this->participantRepository->findOneBy(['key_hash' => $room_key->getKeyHash()]);
        if (!empty(trim($message->getMessage())) && $participant) {
            $participant->setRoomKeyObj($room_key);
            $chat_message = $this->messageEncoder->encode(
                $participant,
                $message->getMessage(),
                $message->getCreatedAt()
            );
            $chat_message->setAuthor($participant);
            $chat_message->setChatroom($participant->getChatroom());
            $this->entityManager->persist($chat_message);
            $this->entityManager->flush();
        }
    }
}
