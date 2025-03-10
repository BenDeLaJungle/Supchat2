<?php

namespace App\Entity;

use App\Repository\WorkspaceMembersRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Workspaces;
use App\Entity\Users;
use App\Entity\Roles;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WorkspaceMembersRepository::class)]
class WorkspaceMembers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Workspaces::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'Entiter est obligatoire.")]
    private ?Workspaces $workspace = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'Entiter est obligatoire.")]
    private ?Users $user = null;

    #[ORM\ManyToOne(targetEntity: Roles::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'Entiter est obligatoire.")]
    private ?Roles $role = null;

    #[ORM\Column(type: 'boolean')]
    #[Assert\NotNull(message: "La valeur ne peut pas être nulle.")]
    #[Assert\Type(type: "bool", message: "La valeur doit être un booléen.")]
    private ?bool $publish = null;

    #[ORM\Column(type: 'boolean')]
    #[Assert\NotNull(message: "La valeur ne peut pas être nulle.")]
    #[Assert\Type(type: "bool", message: "La valeur doit être un booléen.")]
    private ?bool $moderate = null;

    #[ORM\Column(type: 'boolean')]
    #[Assert\NotNull(message: "La valeur ne peut pas être nulle.")]
    #[Assert\Type(type: "bool", message: "La valeur doit être un booléen.")]
    private ?bool $manage = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(Users $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getRole(): ?Roles
    {
        return $this->role;
    }

    public function setRole(Roles $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function canPublish(): ?bool
    {
        return $this->publish;
    }

    public function setPublish(bool $publish): self
    {
        if (!is_bool($publish) && !is_null($publish)) {
            throw new \InvalidArgumentException("doit être strictement true, false ou null.");
        }
        $this->publish = $publish;
        return $this;
    }

    public function canModerate(): ?bool
    {
        return $this->moderate;
    }

    public function setModerate(bool $moderate): self
    {
        if (!is_bool($moderate) && !is_null($moderate)) {
            throw new \InvalidArgumentException("doit être strictement true, false ou null.");
        }
        $this->moderate = $moderate;
        return $this;
    }

    public function canManage(): ?bool
    {
        return $this->manage;
    }

    public function setManage(bool $manage): self
    {
        if (!is_bool($manage) && !is_null($manage)) {
            throw new \InvalidArgumentException("doit être strictement true, false ou null.");
        }
        $this->manage = $manage;
        return $this;
    }
}

