<?php

namespace App\Entity;

use App\Repository\ChatMessageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ChatMessageRepository::class)
 */
class ChatMessage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $encryptedMessage;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nonce;

    /**
     * @ORM\Column(type="smallint", options={"default":"0"})
     */
    private $read_count = 0;

    /**
     * @ORM\ManyToOne(targetEntity=Participant::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity=Chatroom::class, inversedBy="chatMessages", cascade={"remove"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $chatroom;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $delete_after;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEncryptedMessage(): ?string
    {
        return $this->encryptedMessage;
    }

    public function setEncryptedMessage(string $encryptedMessage): self
    {
        $this->encryptedMessage = $encryptedMessage;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getNonce(): ?string
    {
        return $this->nonce;
    }

    public function setNonce(string $nonce): self
    {
        $this->nonce = $nonce;

        return $this;
    }

    public function getReadCount(): ?int
    {
        return $this->read_count;
    }

    public function setReadCount(int $read_count): self
    {
        $this->read_count = $read_count;

        return $this;
    }

    public function getAuthor(): ?Participant
    {
        return $this->author;
    }

    public function setAuthor(?Participant $author): self
    {
        $this->author = $author;

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

    public function getDeleteAfter(): ?\DateTimeInterface
    {
        return $this->delete_after;
    }

    public function setDeleteAfter(?\DateTimeInterface $delete_after): self
    {
        $this->delete_after = $delete_after;

        return $this;
    }
}
