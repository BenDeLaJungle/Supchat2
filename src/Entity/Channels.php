<?php

namespace App\Entity;

use App\Repository\ChannelsRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Workspaces;

#[ORM\Entity(repositoryClass: ChannelsRepository::class)]
class Channels
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Workspaces::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Workspaces $workspace = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $status = null; // true for public, false for private

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

    public function getWorkspace(): ?Workspaces
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspaces $workspace): self
    {
        $this->workspace = $workspace;
        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;
        return $this;
    }
}

