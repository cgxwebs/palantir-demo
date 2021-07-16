<?php


namespace App\DomainService;


use App\Entity\Chatroom;
use App\Entity\Participant;
use App\Enum\ChatroomStatus;
use App\ValueObject\RoomKey;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ChatroomManager
{
    private const DEFAULT_FIRST_PARTICIPANT_NAME = [
        'Blue', 'Gold', 'Green', 'White', 'Violet'
    ];
    private const DEFAULT_SECOND_PARTICIPANT_NAME = [
        'Red', 'Silver', 'Yellow', 'Black', 'Indigo'
    ];

    public function __construct(private SerializerInterface $serializer, private TotpAuthenticatorInterface $totpAuthenticator)
    {
    }

    public function createRoom($days_valid = 7, $creator_info = array())
    {
        $first_participant = $this->createParticipant($this->getRandomName('first'));
        $second_participant = $this->createParticipant($this->getRandomName('second'));

        $fp_roomkey = $first_participant->getRoomKeyObj();
        $sp_roomkey = $second_participant->getRoomKeyObj();

        $fp_roomkey->setRecipientKey($sp_roomkey->getPublicKey());
        $sp_roomkey->setRecipientKey($fp_roomkey->getPublicKey());

        $first_participant->setRoomKey($this->serializer->serialize($fp_roomkey, 'json'));
        $second_participant->setRoomKey($this->serializer->serialize($sp_roomkey, 'json'));

        $chatroom = new Chatroom();
        $chatroom->setName($this->generateChatroomName($fp_roomkey, $sp_roomkey));

        $today = new \DateTime();
        $expires = clone $today;
        $chatroom->setCreatedAt($today);
        $chatroom->setExpiresOn($expires->add(new \DateInterval(sprintf("P%dD", $days_valid))));

        $chatroom->addParticipant($first_participant);
        $chatroom->addParticipant($second_participant);
        
        $chatroom->setCreatorId($creator_info['id']);
        $chatroom->setCreatorInfo($creator_info);

        return $chatroom;
    }

    public function createParticipant(string $name)
    {
        $roomkey = new RoomKey(sodium_crypto_box_keypair());
        $participant = new Participant();
        $participant->setName($name);
        $participant->setRoomKeyObj($roomkey);
        $participant->setKeyHash($roomkey->getKeyHash());
        $participant->setTotpSecret($this->totpAuthenticator->generateSecret());
        return $participant;
    }

    public function getRandomName($which_pair = 'first')
    {
        if ($which_pair == 'first') {
            return self::DEFAULT_FIRST_PARTICIPANT_NAME[rand(0, count(self::DEFAULT_FIRST_PARTICIPANT_NAME) - 1)];
        }
        return self::DEFAULT_SECOND_PARTICIPANT_NAME[rand(0, count(self::DEFAULT_SECOND_PARTICIPANT_NAME) - 1)];
    }

    private function generateChatroomName(RoomKey $first, RoomKey $second)
    {
        return hash('sha3-256', $first->getKeyHash() . $second->getKeyHash());
    }
}