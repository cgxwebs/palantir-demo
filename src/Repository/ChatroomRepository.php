<?php

namespace App\Repository;

use App\Entity\Chatroom;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Chatroom|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chatroom|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chatroom[]    findAll()
 * @method Chatroom[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatroomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chatroom::class);
    }

    public function removeChatroomByFacebookId(string $facebook_id)
    {
        $dql = "DELETE FROM {$this->getEntityName()} c 
            WHERE c.creator_id = :creator_id";

        return $this->getEntityManager()
            ->createQuery($dql)
            ->execute([
                'creator_id' => $facebook_id
            ]);
    }

    public function removeExpiredChatrooms()
    {
        $dql = "DELETE FROM {$this->getEntityName()} m WHERE m.expires_on <= :today";

        return $this->getEntityManager()
            ->createQuery($dql)
            ->execute([
                'today' => new \DateTime()
            ]);
    }

}
