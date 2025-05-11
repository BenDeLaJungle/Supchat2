<?php

namespace App\Entity;

use App\Repository\ChannelOptionsRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Channels;
use App\Entity\Users;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChannelOptionsRepository::class)]
class ChannelOptions
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

    #[ORM\Column(type: 'boolean')]
    #[Assert\NotNull(message: "La valeur peut pas être nulle.")]
    #[Assert\Type(type: "bool", message: "La valeur doit être un booléen.")]
    private ?bool $pushUp = null;

    #[ORM\Column(type: 'boolean')]
    #[Assert\NotNull(message: "La valeur ne peut pas être nulle.")]
    #[Assert\Type(type: "bool", message: "La valeur doit être un booléen.")]
    private ?bool $mail = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide.")]
    #[Assert\Type(type: "string", message: "Le nom doit être une chaîne de caractères.")]
    #[Assert\Choice(choices: ["all","mention","none"], message: " doit être all mention none.")]
    private ?string $notification = null;

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

    public function getPushUp(): ?bool
    {
        return $this->pushUp;
    }

    public function setPushUp(bool $pushUp): self
    {
        if (!is_bool($pushUp) && !is_null($pushUp)) {
            throw new \InvalidArgumentException("doit être strictement true, false ou null.");
        }
        $this->pushUp = $pushUp;
        return $this;
    }

    public function getMail(): ?bool
    {
        return $this->mail;
    }

    public function setMail(bool $mail): self
    {
        if (!is_bool($mail) && !is_null($mail)) {
            throw new \InvalidArgumentException("doit être strictement true, false ou null.");
        }
        $this->mail = $mail;
        return $this;
    }

    public function getNotification(): ?string
    {
        return $this->notification;
    }

    public function setNotification(string $notification): self
    {
        $this->notification = $notification;
        return $this;
    }
}

