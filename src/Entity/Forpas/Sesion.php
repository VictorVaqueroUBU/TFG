<?php

namespace App\Entity\Forpas;

use App\Repository\Forpas\SesionRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SesionRepository::class)]
class Sesion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $fecha = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?DateTimeInterface $hora_inicio = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $duracion = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observaciones = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $tipo = null;

    #[ORM\ManyToOne(inversedBy: '$sesionesEdicion')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Edicion $edicion = null;

    #[ORM\ManyToOne(inversedBy: '$sesiones')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Formador $formador = null;

    /**
     * @var Collection<int, Asistencia>
     */
    #[ORM\OneToMany(targetEntity: Asistencia::class, mappedBy: 'sesion', orphanRemoval: true)]
    private Collection $asistencias;

    public function __construct()
    {
        $this->asistencias = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFecha(): ?DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(DateTimeInterface $fecha): static
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getHoraInicio(): ?DateTimeInterface
    {
        return $this->hora_inicio;
    }

    public function setHoraInicio(DateTimeInterface $hora_inicio): static
    {
        $this->hora_inicio = $hora_inicio;

        return $this;
    }

    public function getDuracion(): ?string
    {
        return $this->duracion;
    }

    public function setDuracion(string $duracion): static
    {
        $this->duracion = $duracion;

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

    public function getTipo(): ?int
    {
        return $this->tipo;
    }

    public function setTipo(int $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getEdicion(): ?Edicion
    {
        return $this->edicion;
    }

    public function setEdicion(?Edicion $edicion): static
    {
        $this->edicion = $edicion;

        return $this;
    }

    public function getFormador(): ?Formador
    {
        return $this->formador;
    }

    public function setFormador(?Formador $formador): static
    {
        $this->formador = $formador;

        return $this;
    }

    /**
     * @return Collection<int, Asistencia>
     */
    public function getAsistencias(): Collection
    {
        return $this->asistencias;
    }

    public function addAsistencia(Asistencia $asistencia): static
    {
        if (!$this->asistencias->contains($asistencia)) {
            $this->asistencias->add($asistencia);
            $asistencia->setSesion($this);
        }

        return $this;
    }

    public function removeAsistencia(Asistencia $asistencia): static
    {
        if ($this->asistencias->removeElement($asistencia)) {
            if ($asistencia->getSesion() === $this) {
                $asistencia->setSesion(null);
            }
        }

        return $this;
    }
}
