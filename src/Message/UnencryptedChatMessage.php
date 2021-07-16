<?php

namespace App\Message;

use App\ValueObject\RoomKey;

final class UnencryptedChatMessage
{
    public function __construct(private string $chatroom_name, private RoomKey $room_key, private string $message, private \DateTimeInterface $created_at)
    {
    }

    /**
     * @return string
     */
    public function getChatroomName(): string
    {
        return $this->chatroom_name;
    }

    /**
     * @return RoomKey
     */
    public function getRoomKey(): RoomKey
    {
        return $this->room_key;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->created_at;
    }
}
