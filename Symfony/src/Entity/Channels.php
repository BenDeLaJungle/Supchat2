<?php 

namespace App\Entity;

use App\Repository\ChannelsRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Workspaces;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChannelsRepository::class)]
class Channels
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Type(type: "string", message: "Le nom doit être une chaîne de caractères.")]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Workspaces::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'Entiter est obligatoire.")]
    private ?Workspaces $workspace = null;

    #[ORM\Column(type: 'boolean')]
    #[Assert\NotNull(message: "La valeur ne peut pas être nulle.")]
    #[Assert\Type(type: "bool", message: "La valeur doit être un booléen.")]
    private ?bool $status = null; // true for public, false for private

    // Rôle minimum requis pour accéder au canal (1 = membre, 2 = modérateur, 3 = admin)
    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private ?int $minRole = 1;

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
        if (!is_bool($status) && !is_null($status)) {
            throw new \InvalidArgumentException("doit être strictement true, false ou null.");
        }
        $this->status = $status;
        return $this;
    }

    public function getMinRole(): ?int
    {
        return $this->minRole;
    }

    public function setMinRole(int $minRole): self
    {
        $this->minRole = $minRole;
        return $this;
    }
}
