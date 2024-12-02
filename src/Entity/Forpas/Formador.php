<?php

namespace App\Entity\Forpas;

use App\Entity\Sistema\Usuario;
use App\Repository\Forpas\FormadorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity(fields: ['nif'], message: 'El NIF indicado ya está dado de alta.')]
#[ORM\Entity(repositoryClass: FormadorRepository::class)]
class Formador
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 9, unique: true)]
    private ?string $nif = null;

    #[ORM\Column(length: 50)]
    private ?string $apellidos = null;

    #[ORM\Column(length: 50)]
    private ?string $nombre = null;

    #[ORM\Column(length: 100)]
    private ?string $organizacion = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $correo_aux = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $telefono = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observaciones = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $formador_RJ = null;

    /**
     * @var Collection<int, FormadorEdicion>
     */
    #[ORM\OneToMany(targetEntity: FormadorEdicion::class, mappedBy: 'formador', orphanRemoval: true)]
    private Collection $formadorEdiciones;

    #[ORM\OneToOne(targetEntity: Usuario::class, inversedBy: 'formador', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $usuario = null;

    public function __construct()
    {
        $this->formadorEdiciones = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNif(): ?string
    {
        return $this->nif;
    }

    public function setNif(string $nif): static
    {
        $this->nif = $nif;

        return $this;
    }

    public function getApellidos(): ?string
    {
        return $this->apellidos;
    }

    public function setApellidos(string $apellidos): static
    {
        $this->apellidos = $apellidos;

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getOrganizacion(): ?string
    {
        return $this->organizacion;
    }

    public function setOrganizacion(string $organizacion): static
    {
        $this->organizacion = $organizacion;

        return $this;
    }

    public function getCorreoAux(): ?string
    {
        return $this->correo_aux;
    }

    public function setCorreoAux(?string $correo_aux): static
    {
        $this->correo_aux = $correo_aux;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): static
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getObservaciones(): ?string
    {
        return $this->observaciones;
    }

    public function setObservaciones(?string $observaciones): static
    {
        $this->observaciones = $observaciones;

        return $this;
    }

    public function getFormadorRJ(): ?int
    {
        return $this->formador_RJ;
    }

    public function setFormadorRJ(?int $formador_RJ): static
    {
        $this->formador_RJ = $formador_RJ;

        return $this;
    }

    /**
     * @return Collection<int, FormadorEdicion>
     */
    public function getFormadorEdiciones(): Collection
    {
        return $this->formadorEdiciones;
    }

    public function addFormadorEdiciones(FormadorEdicion $formadorEdicion): static
    {
        if (!$this->formadorEdiciones->contains($formadorEdicion)) {
            $this->formadorEdiciones->add($formadorEdicion);
        }

        return $this;
    }

    public function removeFormadorEdiciones(FormadorEdicion $formadorEdicion): static
    {
        if ($this->formadorEdiciones->removeElement($formadorEdicion)) {
            // set the owning side to null (unless already changed)
            if ($formadorEdicion->getFormador() === $this) {
                $formadorEdicion->setFormador(null);
            }
        }

        return $this;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(Usuario $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }
}
