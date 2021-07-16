<?php

namespace App\EventSubscriber;

use App\Entity\Participant;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class ChatroomPassportCheckSubscriber implements EventSubscriberInterface
{
    private ?Request $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function onCheckPassportEvent(CheckPassportEvent $event)
    {
        if ($event instanceof SelfValidatingPassport) {
            $chatroom_name = $this->request->attributes->get('name');
            /**
             * @var Participant $participant
             */
            $participant = $event->getPassport()->getBadge(UserBadge::class)->getUser();

            if (empty($participant) || $participant->getChatroom()->getName() !== $chatroom_name) {
                throw new BadCredentialsException();
            }

            $today = new \DateTime();
            if ($today > $participant->getChatroom()->getExpiresOn()) {
                throw new CredentialsExpiredException();
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            CheckPassportEvent::class => 'onCheckPassportEvent',
        ];
    }
}
