<?php


namespace App\Enum;


final class ChatroomStatus
{
    public const ACTIVE = 'active';
    public const PENDING = 'new';
    public const AWAITING_RECIPIENT = 'awaiting_recipient';
}