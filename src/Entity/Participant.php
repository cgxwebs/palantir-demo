<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use App\ValueObject\RoomKey;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=ParticipantRepository::class)
 */
class Participant implements UserInterface, TwoFactorInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=128, unique=true)
     */
    private $key_hash;

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private $room_key;

    private $room_key_obj;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\ManyToOne(targetEntity=Chatroom::class, inversedBy="participants")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $chatroom;

    /**
     * @ORM\Column(name="totpSecret", type="string", nullable=true)
     */
    private $totpSecret;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getKeyHash(): ?string
    {
        return $this->key_hash;
    }

    public function setKeyHash(string $key_hash): self
    {
        $this->key_hash = $key_hash;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->key_hash;
    }

    public function getRoomKey(): ?string
    {
        return $this->room_key;
    }

    public function setRoomKey(string $room_key): self
    {
        $this->room_key = $room_key;

        return $this;
    }

    public function getChatroom(): ?Chatroom
    {
        return $this->chatroom;
    }

    public function setChatroom(?Chatroom $chatroom): self
    {
        $this->chatroom = $chatroom;

        return $this;
    }

    /**
     * @return RoomKey
     */
    public function getRoomKeyObj()
    {
        return $this->room_key_obj;
    }

    /**
     * @param RoomKey $room_key_obj
     */
    public function setRoomKeyObj(RoomKey $room_key_obj): void
    {
        $this->room_key_obj = $room_key_obj;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        $roles[] = 'ROLE_CHAT_PARTICIPANT';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * This method can be removed in Symfony 6.0 - is not needed for apps that do not check user passwords.
     *
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return null;
    }

    /**
     * This method can be removed in Symfony 6.0 - is not needed for apps that do not check user passwords.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {

    }

    public function getUsername(): string
    {
        return $this->key_hash;
    }

    public function isTotpAuthenticationEnabled(): bool
    {
        return $this->totpSecret ? true : false;
    }

    public function getTotpAuthenticationUsername(): string
    {
        return $this->key_hash;
    }

    public function getTotpAuthenticationConfiguration(): TotpConfigurationInterface
    {
        return new TotpConfiguration($this->totpSecret, TotpConfiguration::ALGORITHM_SHA1, 30, 6);
    }

    public function setTotpSecret(?string $totpSecret): void
    {
        $this->totpSecret = $totpSecret;
    }

    public function rebuildRoomKey(Request $request): void
    {
        $pkey = RoomKey::decode($request->getSession()->get('participant_key'));
        $rkey = RoomKey::decode($request->getSession()->get('recipient_key'));
        $room_key = new RoomKey($pkey);
        $room_key->setRecipientKey($rkey);
        $this->setRoomKeyObj($room_key);
    }
}
