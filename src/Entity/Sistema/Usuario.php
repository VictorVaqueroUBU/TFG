<?php

namespace App\Entity\Sistema;

use App\Entity\Forpas\Formador;
use App\Entity\Forpas\Participante;
use App\Repository\Sistema\UsuarioRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['email'], message: 'Ya existe una cuenta con este correo electrónico')]
#[UniqueEntity(fields: ['username'], message: 'Ya existe una cuenta con ese nombre de usuario')]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true, nullable: false)]
    private string $username;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(nullable: false)]
    private string $password;

    #[ORM\Column(length: 50)]
    private string $email;

    #[ORM\Column]
    private bool $verified;

    #[ORM\Column(nullable: false)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $passwordChangedAt = null;

    #[ORM\OneToOne(targetEntity: Participante::class, mappedBy: 'usuario', cascade: ['persist'])]
    private ?Participante $participante = null;

    #[ORM\OneToOne(targetEntity: Formador::class, mappedBy: 'usuario', cascade: ['persist'])]
    private ?Formador $formador = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        return array_unique($this->roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }
    public function isVerified(): ?bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): static
    {
        $this->verified = $verified;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
    
    public function getPasswordChangedAt(): ?DateTimeInterface
    {
        return $this->passwordChangedAt;
    }

    public function setPasswordChangedAt(?DateTimeInterface $passwordChangedAt): static
    {
        $this->passwordChangedAt = $passwordChangedAt;

        return $this;
    }
    
    public function getParticipante(): ?Participante
    {
        return $this->participante;
    }

    public function setParticipante(Participante $participante): static
    {
        // set the owning side of the relation if necessary
        if ($participante->getUsuario() !== $this) {
            $participante->setUsuario($this);
        }

        $this->participante = $participante;

        return $this;
    }

    public function getFormador(): ?Formador
    {
        return $this->formador;
    }

    public function setFormador(Formador $formador): static
    {
        // set the owning side of the relation if necessary
        if ($formador->getUsuario() !== $this) {
            $formador->setUsuario($this);
        }

        $this->formador = $formador;

        return $this;
    }
}
