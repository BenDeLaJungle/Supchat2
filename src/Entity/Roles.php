<?php

namespace App\Entity;

use App\Repository\RolesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RolesRepository::class)]
class Roles
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $publish = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $moderate = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $manage = null;

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

    public function canPublish(): ?bool
    {
        return $this->publish;
    }

    public function setPublish(bool $publish): self
    {
        $this->publish = $publish;
        return $this;
    }

    public function canModerate(): ?bool
    {
        return $this->moderate;
    }

    public function setModerate(bool $moderate): self
    {
        $this->moderate = $moderate;
        return $this;
    }

    public function canManage(): ?bool
    {
        return $this->manage;
    }

    public function setManage(bool $manage): self
    {
        $this->manage = $manage;
        return $this;
    }
}
