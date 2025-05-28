<?php

namespace App\Entity;

use App\Repository\RolesRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RolesRepository::class)]
class Roles
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
    private ?string $name = null;

    #[ORM\Column(type: 'boolean')]
    #[Assert\NotNull(message: "La valeur ne peut pas être nulle.")]
    #[Assert\Type(type: "bool", message: "La valeur doit être un booléen.")]
    private bool $publish;

    #[ORM\Column(type: 'boolean')]
    #[Assert\NotNull(message: "La valeur ne peut pas être nulle.")]
    #[Assert\Type(type: "bool", message: "La valeur doit être un booléen.")]
    private bool $moderate;

    #[ORM\Column(type: 'boolean')]
    #[Assert\NotNull(message: "La valeur ne peut pas être nulle.")]
    #[Assert\Type(type: "bool", message: "La valeur doit être un booléen.")]
    private bool $manage;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
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

    public static function hasPermission(int $roleId, string $action): bool
    {
        $permissions = [
            'create_workspace'    => [1, 2, 3],
            'create_channel'      => [2, 3],
            'manage_members'      => [2, 3],
            'manage_roles'        => [3],
            'delete_workspace'    => [3],
        ];

        return in_array($roleId, $permissions[$action] ?? []);
    }
}
