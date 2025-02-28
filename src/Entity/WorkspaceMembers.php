<?php

namespace App\Entity;

use App\Repository\WorkspaceMembersRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Workspaces;
use App\Entity\Users;
use App\Entity\Roles;

#[ORM\Entity(repositoryClass: WorkspaceMembersRepository::class)]
class WorkspaceMembers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Workspaces::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Workspaces $workspace = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user = null;

    #[ORM\ManyToOne(targetEntity: Roles::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Roles $role = null;

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

