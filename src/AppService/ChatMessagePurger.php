<?php


namespace App\AppService;


use App\Entity\ChatMessage;
use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;

class ChatMessagePurger
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function markMessagesAsRead(Participant $participant, array $messages): void
    {
        /**
         * @var ChatMessage $m
         */
        $next_expiry = new \DateTime();
        $next_expiry->add(new \DateInterval('P1D'));
        foreach ($messages as $m) {
            $delete_after = $m->getDeleteAfter();
            $has_expiry = is_object($delete_after) && $delete_after instanceof \DateTimeInterface;
            if ($m->getAuthor()->getKeyHash() !== $participant->getKeyHash() && !$has_expiry) {
                $m->setDeleteAfter($next_expiry);
                $this->entityManager->persist($m);
            }
        }
        $this->entityManager->flush();
    }
}