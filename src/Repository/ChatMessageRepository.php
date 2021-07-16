<?php

namespace App\Repository;

use App\Entity\ChatMessage;
use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ChatMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChatMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChatMessage[]    findAll()
 * @method ChatMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatMessage::class);
    }

    public function findUnreadMessages(Participant $participant)
    {
        $dql = "SELECT m FROM {$this->getEntityName()} m 
            WHERE m.chatroom = :chatroom AND 
                  (m.delete_after IS NULL OR m.delete_after >= :today)
            ORDER BY m.created_at DESC
            ";

        return $this->getEntityManager()
            ->createQuery($dql)
            ->execute([
                'chatroom' => $participant->getChatroom(),
                'today' => new \DateTime()
            ]);
    }

    public function removeExpiredMessages()
    {
        $dql = "DELETE FROM {$this->getEntityName()} m WHERE m.delete_after <= :today";

        return $this->getEntityManager()
            ->createQuery($dql)
            ->execute([
                'today' => new \DateTime()
            ]);
    }
}
