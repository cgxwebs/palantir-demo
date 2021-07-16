<?php

namespace App\EventSubscriber;

use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class ChatroomOnSigninSubscriber implements EventSubscriberInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function onLoginSuccessEvent(LoginSuccessEvent $event)
    {
        /**
         * @var Participant $user
         */
        $user = $event->getUser();
        if (!empty($user->getRoomKey())) {
            $user->setRoomKey('');
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccessEvent',
        ];
    }
}
