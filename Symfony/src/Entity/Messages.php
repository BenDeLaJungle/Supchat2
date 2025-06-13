<?php

namespace App\Entity;

use App\Repository\MessagesRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Channels;
use App\Entity\Users;
use App\Entity\Hashtags;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: MessagesRepository::class)]
class Messages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Channels::class)]
    #[ORM\JoinColumn(nullable: true)] 
    private ?Channels $channel = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'utilisateur est obligatoire.")]
    private ?Users $user = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Users $recipient = null;

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

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'message', targetEntity: Hashtags::class)]
    private Collection $hashtags;

    #[ORM\OneToMany(mappedBy: 'message', targetEntity: Notifications::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $notifications;

    public function __construct()
    {
        $this->hashtags = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChannel(): ?Channels
    {
        return $this->channel;
    }

    public function setChannel(?Channels $channel): self
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

    public function getRecipient(): ?Users
    {
        return $this->recipient;
    }

    public function setRecipient(?Users $recipient): self
    {
        $this->recipient = $recipient;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    public function getHashtags(): Collection
    {
        return $this->hashtags;
    }

    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notifications $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setMessage($this);
        }

        return $this;
    }

    public function removeNotification(Notifications $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            if ($notification->getMessage() === $this) {
                $notification->setMessage(null);
            }
        }

        return $this;
    }
}
