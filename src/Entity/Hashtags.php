<?php

namespace App\Entity;

use App\Repository\HashtagsRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Channels;
use App\Entity\Messages;

#[ORM\Entity(repositoryClass: HashtagsRepository::class)]
class Hashtags
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Channels::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Channels $channel = null;

    #[ORM\ManyToOne(targetEntity: Messages::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Messages $message = null;

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

    public function getMessage(): ?Messages
    {
        return $this->message;
    }

    public function setMessage(Messages $message): self
    {
        $this->message = $message;
        return $this;
    }
}

