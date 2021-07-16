<?php

namespace App\Entity;

use App\Repository\ChatroomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ChatroomRepository::class)
 */
class Chatroom
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expires_on;

    /**
     * @ORM\OneToMany(targetEntity=Participant::class, mappedBy="chatroom", orphanRemoval=true, cascade={"persist"})
     */
    private $participants;

    /**
     * @ORM\OneToMany(targetEntity=ChatMessage::class, mappedBy="chatroom", orphanRemoval=true)
     */
    private $chatMessages;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $creator_info = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $creator_id;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->chatMessages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getExpiresOn(): ?\DateTimeInterface
    {
        return $this->expires_on;
    }

    public function setExpiresOn(\DateTimeInterface $expires_on): self
    {
        $this->expires_on = $expires_on;

        return $this;
    }

    /**
     * @return Collection|Participant[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
            $participant->setChatroom($this);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): self
    {
        if ($this->participants->removeElement($participant)) {
            // set the owning side to null (unless already changed)
            if ($participant->getChatroom() === $this) {
                $participant->setChatroom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ChatMessage[]
     */
    public function getChatMessages(): Collection
    {
        return $this->chatMessages;
    }

    public function addChatMessage(ChatMessage $chatMessage): self
    {
        if (!$this->chatMessages->contains($chatMessage)) {
            $this->chatMessages[] = $chatMessage;
            $chatMessage->setChatroom($this);
        }

        return $this;
    }

    public function removeChatMessage(ChatMessage $chatMessage): self
    {
        if ($this->chatMessages->removeElement($chatMessage)) {
            // set the owning side to null (unless already changed)
            if ($chatMessage->getChatroom() === $this) {
                $chatMessage->setChatroom(null);
            }
        }

        return $this;
    }

    public function getCreatorInfo(): ?array
    {
        return $this->creator_info;
    }

    public function setCreatorInfo(?array $creator_info): self
    {
        $this->creator_info = $creator_info;

        return $this;
    }

    public function getCreatorId(): ?string
    {
        return $this->creator_id;
    }

    public function setCreatorId(?string $creator_id): self
    {
        $this->creator_id = $creator_id;

        return $this;
    }
}
