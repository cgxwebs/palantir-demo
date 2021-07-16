<?php


namespace App\DomainService;


use App\Entity\ChatMessage;
use App\Entity\Participant;

class MessageEncoder
{
    public function encode(Participant $participant, string $message, \DateTimeInterface $created_at): ChatMessage
    {
        $nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);
        $room_key = $participant->getRoomKeyObj();
        $encrypted = sodium_crypto_box($message, $nonce, $room_key->getMessageKey());
        $chat_message = new ChatMessage();
        $chat_message->setCreatedAt($created_at);
        $chat_message->setNonce(sodium_bin2hex($nonce));
        $chat_message->setEncryptedMessage(sodium_bin2hex($encrypted));
        return $chat_message;
    }

    public function decode(Participant $participant, ChatMessage $message)
    {
        $nonce = sodium_hex2bin($message->getNonce());
        $room_key = $participant->getRoomKeyObj();
        $bin_message = sodium_hex2bin($message->getEncryptedMessage());
        return sodium_crypto_box_open($bin_message, $nonce, $room_key->getMessageKey());
    }
}