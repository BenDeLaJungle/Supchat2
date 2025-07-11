<?php

namespace App\Entity;

use App\Repository\FilesRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Messages;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FilesRepository::class)]
class Files
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Messages::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'Entiter est obligatoire.")]
    private ?Messages $message = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le chemin du fichier ne peut pas être vide.")]
    #[Assert\Length(
    max: 255,
    maxMessage: "Le chemin du fichier ne peut pas dépasser {{ limit }} caractères."
)]
    private ?string $filePath = null;


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

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }
}

