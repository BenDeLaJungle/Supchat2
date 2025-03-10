<?php

namespace App\Entity;

use App\Repository\MentionsRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Messages;
use App\Entity\Users;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MentionsRepository::class)]
class Mentions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Messages::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'Entiter est obligatoire.")]
    private ?Messages $message = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'Entiter est obligatoire.")]
    private ?Users $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?Messages
    {
        return $this->message;
    }

    public function setMessage(Messages $message): self
    {
        $this->message = $message;
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
}

