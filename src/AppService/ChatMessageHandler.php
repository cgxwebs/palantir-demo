<?php


namespace App\AppService;


use App\DomainService\MessageEncoder;
use App\Message\UnencryptedChatMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Messenger\MessageBusInterface;

class ChatMessageHandler
{
    public function __construct(private MessageEncoder $messageEncoder, private MessageBusInterface $messageBus)
    {
    }

    public function decodeMessages($user, $messages)
    {
        $decoded_messages = new ArrayCollection();
        foreach ($messages as $message) {
            $decoded = 'Error: Failed to decode message.';
            try {
                $raw_decode = $this->messageEncoder->decode($user, $message);
                if (false !== $decoded && !is_null($decoded)) {
                    $decoded = $raw_decode;
                }
            } catch (\Throwable) {
            }
            $decoded_messages->add([
                'message' => $message,
                'decoded' => $decoded
            ]);
        }
        return $decoded_messages;
    }

    public function sendMessage($user, $message)
    {
        $message = trim(mb_substr($message, 0, 1000));
        $unencrypted = new UnencryptedChatMessage(
            $user->getChatroom()->getName(),
            $user->getRoomKeyObj(),
            $message,
            new \DateTime()
        );
        $this->messageBus->dispatch($unencrypted);
    }
}