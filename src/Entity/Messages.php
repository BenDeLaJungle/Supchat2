<?php

namespace App\Entity;

use App\Repository\MessagesRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Channels;
use App\Entity\Users;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MessagesRepository::class)]
class Messages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Channels::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'Entiter est obligatoire.")]
    private ?Channels $channel = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'Entiter est obligatoire.")]
    private ?Users $user = null;


    #[ORM\Column(type: 'text')]

    #[Assert\NotBlank(message: "Le message ne peut pas être vide.")]
    #[Assert\Length(
        min: 1,
        max: 5000,
        minMessage: "Le message doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le message ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Type(type: "string", message: "Le contenu doit être une chaîne de caractères.")]
    private ?string $content = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChannel(): ?Channels
    {
        return $this->channel;
    }

    public function setChannel(Channels $channel): self
    {
        $this->channel = $channel;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }
}

