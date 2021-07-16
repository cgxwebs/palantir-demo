<?php

namespace App\Entity;

use App\Repository\DataDeletionLogRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DataDeletionLogRepository::class)
 */
class DataDeletionLog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $meta = [];

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $confirmation_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }

    public function setMeta(?array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function getConfirmationId(): ?string
    {
        return $this->confirmation_id;
    }

    public function setConfirmationId(string $confirmation_id): self
    {
        $this->confirmation_id = $confirmation_id;

        return $this;
    }
}
