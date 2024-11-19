<?php

namespace App\Entity\Forpas;

use App\Repository\Forpas\UsuarioRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    private ?string $correo1 = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $correo2 = null;

    #[ORM\OneToOne(mappedBy: 'usuario', cascade: ['persist', 'remove'])]
    private ?Participante $participante = null;

    #[ORM\OneToOne(mappedBy: 'usuario', cascade: ['persist', 'remove'])]
    private ?Formador $formador = null;
    public function getId(): ?int
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
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
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

    public function getCorreo1(): ?string
    {
        return $this->correo1;
    }

    public function setCorreo1(string $correo1): static
    {
        $this->correo1 = $correo1;

        return $this;
    }

    public function getCorreo2(): ?string
    {
        return $this->correo2;
    }

    public function setCorreo2(?string $correo2): static
    {
        $this->correo2 = $correo2;

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
