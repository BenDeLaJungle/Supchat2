<?php

namespace App\Entity;


use App\Repository\UsersRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
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
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Type(type: "string", message: "Le nom doit être une chaîne de caractères.")]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Type(type: "string", message: "Le nom doit être une chaîne de caractères.")]
    private ?string $userName = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: "ne peut pas être vide.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "doit contenir au moins {{ limit }} caractères.",
        maxMessage: "ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Type(type: "string", message: "doit être une chaîne de caractères.")]
    private ?string $emailAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(min: 8, max: 255, message: "Le mot de passe doit contenir au moins {{ limit }} caractères.")]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le rôle ne peut pas être vide.")]
    #[Assert\Choice(choices: ["ROLE_USER", "ROLE_ADMIN"], message: "Rôle invalide.")]
    private ?string $role = "ROLE_USER";

    #[ORM\Column(type: 'boolean')]
    #[Assert\NotNull(message: "La valeur ne peut pas être nulle.")]
    #[Assert\Type(type: "bool", message: "La valeur doit être un booléen.")]
    private ?bool $theme = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "ne peut pas être vide.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "doit contenir au moins {{ limit }} caractères.",
        maxMessage: "ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Type(type: "string", message: "doit être une chaîne de caractères.")]
    private ?string $status = null;
    
	#[ORM\Column(length: 255, nullable: true)]
    private ?string $oauthProvider = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $oauthID = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): self
    {
        $this->userName = $userName;
        return $this;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): self
    {
        $this->emailAddress = $emailAddress;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password ?? '';
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
{
    if (!in_array($role, ["ROLE_USER", "ROLE_ADMIN"])) {
        throw new \InvalidArgumentException("Rôle invalide.");
    }
    $this->role = $role;
    return $this;
}

    public function getTheme(): ?bool
    {
        return $this->theme;
    }

    public function setTheme(bool $theme): self
    {
        if (!is_bool($theme) && !is_null($theme)) {
            throw new \InvalidArgumentException("doit être strictement true, false ou null.");
        }
        $this->theme = $theme;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getOauthProvider(): ?string
    {
        return $this->oauthProvider;
    }

    public function setOauthProvider(?string $oauthProvider): self
    {
        $this->oauthProvider = $oauthProvider;
        return $this;
    }

    public function getOauthID(): ?string
    {
        return $this->oauthID;
    }

    public function setOauthID(?string $oauthID): self
    {
        $this->oauthID = $oauthID;
        return $this;
    }
	 /**
     * Symfony attend un tableau de rôles, même si l'utilisateur n'en a qu'un.
     */
    public function getRoles(): array
    {
        return [$this->role];
    }
	public function getUserIdentifier(): string
	{
		return $this->emailAddress ?? throw new \LogicException('L\'utilisateur doit avoir un email.');
	}
	public function eraseCredentials(): void
{
    // Supprimer ici toute donnée sensible stockée temporairement (ex: mot de passe en clair).
}
}

