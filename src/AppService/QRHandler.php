<?php


namespace App\AppService;


use Endroid\QrCode\QrCode;

class QRHandler
{
    public function createRoomKeyQR($participant)
    {
        $room_key = json_decode($participant->getRoomKey(), true);
        $roomkey_qr_code = new QrCode(base64_encode(json_encode([
            'key_hash' => $room_key['key_hash'],
            'participant_key' => $room_key['participant_key'],
            'recipient_key' => $room_key['recipient_key'],
        ])));
        $roomkey_qr_code->setSize(500);
        $roomkey_qr_code->setMargin(5);
        $roomkey_qr_code->setLabel("This is your ROOM KEY, store securely and do not share", 12);
        return $roomkey_qr_code;
    }
}