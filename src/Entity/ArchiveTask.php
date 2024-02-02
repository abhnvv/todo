<?php

namespace App\Entity;

use App\Repository\ArchiveTaskRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: ArchiveTaskRepository::class)]
#[Broadcast]
class ArchiveTask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $taskId = null;

    #[ORM\Column(length: 255)]
    private ?string $userId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTaskId(): ?string
    {
        return $this->taskId;
    }

    public function setTaskId(string $taskId): static
    {
        $this->taskId = $taskId;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): static
    {
        $this->userId = $userId;

        return $this;
    }
}
