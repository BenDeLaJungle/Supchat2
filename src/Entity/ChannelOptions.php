<?php

namespace App\Entity;

use App\Repository\ChannelOptionsRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Channels;
use App\Entity\Users;

#[ORM\Entity(repositoryClass: ChannelOptionsRepository::class)]
class ChannelOptions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Channels::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Channels $channel = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $pushUp = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $mail = null;

    #[ORM\Column(length: 255)]
    private ?string $notification = null; // Values: all, mention, none

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
        $this->pushUp = $pushUp;
        return $this;
    }

    public function getMail(): ?bool
    {
        return $this->mail;
    }

    public function setMail(bool $mail): self
    {
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

