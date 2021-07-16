<?php


namespace App\ValueObject;


use Symfony\Component\Serializer\Annotation\Ignore;

final class RoomKey
{
    private ?string $key_hash;
    private ?string $participant_key;
    private ?string $recipient_key;

    public function __construct($participant_key)
    {
        $this->participant_key = sodium_bin2hex($participant_key);
        $this->key_hash = hash('sha3-256', $this->participant_key);
    }

    public function setRecipientKey(string $recipient_key): void
    {
        $this->recipient_key = sodium_bin2hex($recipient_key);
    }

    public function getKeyHash(): string|null
    {
        return $this->key_hash;
    }

    public function getParticipantKey(): ?string
    {
        return $this->participant_key;
    }

    public function getRecipientKey(): ?string
    {
        return $this->recipient_key;
    }

    #[Ignore]
    public function getPublicKey()
    {
        $decoded = sodium_hex2bin($this->getParticipantKey());
        return sodium_crypto_box_publickey($decoded);
    }

    #[Ignore]
    public function getSecretKey()
    {
        $decoded = sodium_hex2bin($this->getParticipantKey());
        return sodium_crypto_box_secretkey($decoded);
    }

    #[Ignore]
    public function getMessageKey()
    {
        $recipient_key = sodium_hex2bin($this->getRecipientKey());
        return sodium_crypto_box_keypair_from_secretkey_and_publickey($this->getSecretKey(), $recipient_key);
    }

    public static function decode($hex)
    {
        return sodium_hex2bin($hex);
    }
}