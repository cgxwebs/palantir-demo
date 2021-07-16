<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Zxing\QrReader;

class PreChatroomSigninSubscriber implements EventSubscriberInterface
{
    public function onKernelRequest(RequestEvent $event)
    {
        if ($event->getRequest()->attributes->get('_route') === 'app_login' &&
            $event->getRequest()->isMethod('POST')) {
            /**
             * @var UploadedFile $room_key
             */
            $room_key = $event->getRequest()->files->get('room_key');
            if ($room_key instanceof UploadedFile) {
                $qr_reader = new QrReader($room_key->getPathname());
                try {
                    $decoded = base64_decode($qr_reader->text());
                    $cont = json_decode($decoded, true, 512, JSON_THROW_ON_ERROR);
                    foreach (['key_hash', 'participant_key', 'recipient_key'] as $item) {
                        if (!empty($cont[$item])) {
                            $$item = trim($cont[$item]);
                        } else {
                            throw new BadCredentialsException('Cannot read QR code image');
                        }
                    }
                } catch (\Throwable) {
                }
            }

            foreach (['key_hash', 'participant_key', 'recipient_key'] as $item) {
                $event->getRequest()->request->set($item, $$item);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.request' => ['onKernelRequest', 9],
        ];
    }
}
