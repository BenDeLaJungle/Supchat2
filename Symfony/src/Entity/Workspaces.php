<?php

namespace App\Entity;

use App\Repository\WorkspacesRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Users;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WorkspacesRepository::class)]
class Workspaces
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "ne peut pas être vide.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "doit contenir au moins {{ limit }} caractères.",
        maxMessage: "ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Type(type: "string", message: "doit être une chaîne de caractères.")]
    private ?string $name = null;

    #[ORM\Column(type: 'boolean')]
    #[Assert\NotNull(message: "La valeur ne peut pas être nulle.")]
    #[Assert\Type(type: "bool", message: "La valeur doit être un booléen.")]
    private ?bool $status = null; // true for public, false for private

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'Entiter est obligatoire.")]
    private ?Users $creator = null;

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

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        if (!is_bool($status) && !is_null($status)) {
            throw new \InvalidArgumentException("doit être strictement true, false ou null.");
        }
        $this->status = $status;
        return $this;
    }

    public function getCreator(): ?Users
    {
        return $this->creator;
    }

    public function setCreator(Users $creator): self
    {
        $this->creator = $creator;
        return $this;
    }
}
