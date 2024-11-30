<?php

namespace App\Entity\Forpas;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Repository\Forpas\EdicionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTimeInterface;
use DateTime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[UniqueEntity(fields: ['codigo_edicion'], message: 'El código de la edición ya existe.')]
#[ORM\Entity(repositoryClass: EdicionRepository::class)]
class Edicion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10, unique: true)]
    private ?string $codigo_edicion = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $fecha_inicio = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $fecha_fin = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $calendario = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $horario = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $lugar = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $estado = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $sesiones = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $max_participantes = null;

    #[ORM\ManyToOne(inversedBy: 'ediciones')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Curso $curso = null;

    /**
     * @var Collection<int, ParticipanteEdicion>
     */
    #[ORM\OneToMany(targetEntity: ParticipanteEdicion::class, mappedBy: 'edicion', orphanRemoval: true)]
    private Collection $participantesEdicion;

    /**
     * @var Collection<int, FormadorEdicion>
     */
    #[ORM\OneToMany(targetEntity: FormadorEdicion::class, mappedBy: 'edicion', orphanRemoval: true)]
    private Collection $formadoresEdicion;

    public function __construct()
    {
        $this->participantesEdicion = new ArrayCollection();
        $this->formadoresEdicion = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodigoEdicion(): ?string
    {
        return $this->codigo_edicion;
    }

    public function setCodigoEdicion(string $codigo_edicion): static
    {
        $this->codigo_edicion = $codigo_edicion;

        return $this;
    }

    public function getFechaInicio(): ?DateTimeInterface
    {
        return $this->fecha_inicio;
    }

    public function setFechaInicio(?DateTimeInterface $fecha_inicio): static
    {
        $this->fecha_inicio = $fecha_inicio;

        return $this;
    }

    public function getFechaFin(): ?DateTimeInterface
    {
        return $this->fecha_fin;
    }

    public function setFechaFin(?DateTimeInterface $fecha_fin): static
    {
        $this->fecha_fin = $fecha_fin;

        return $this;
    }

    public function getCalendario(): ?string
    {
        return $this->calendario;
    }

    public function setCalendario(?string $calendario): static
    {
        $this->calendario = $calendario;

        return $this;
    }

    public function getHorario(): ?string
    {
        return $this->horario;
    }

    public function setHorario(?string $horario): static
    {
        $this->horario = $horario;

        return $this;
    }

    public function getLugar(): ?string
    {
        return $this->lugar;
    }

    public function setLugar(?string $lugar): static
    {
        $this->lugar = $lugar;

        return $this;
    }

    public function getEstado(): ?int
    {
        return $this->estado;
    }

    public function setEstado(int $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    public function getSesiones(): ?int
    {
        return $this->sesiones;
    }

    public function setSesiones(int $sesiones): static
    {
        $this->sesiones = $sesiones;

        return $this;
    }

    public function getMaxParticipantes(): ?int
    {
        return $this->max_participantes;
    }

    public function setMaxParticipantes(int $max_participantes): static
    {
        $this->max_participantes = $max_participantes;

        return $this;
    }

    public function getPlazasLibres(): int
    {
        return $this->getMaxParticipantes() - count($this->getParticipantesEdicion());
    }

    public function getAccion(Participante $participante, UrlGeneratorInterface $urlGenerator): string
    {
        $hoy = (new DateTime())->setTime(0, 0);

        // Caso 1: Usuario no activo
        if ($participante->getUnidad() === null || $participante->getUnidad() === '') {
            return 'Usuario no activo';
        }

        // Obtenemos el registro de participanteEdición en la que está inscrito el participante de ese curso actual
        $edicionInscrita = $participante->getParticipanteEdiciones()->filter(function ($participanteEdicion) {
            return $participanteEdicion->getEdicion()->getCurso() === $this->getCurso();
        })->first();

        // Caso 2: Cancelar inscripción
        if ($edicionInscrita && $edicionInscrita->getEdicion() === $this && $this->getFechaInicio() >= $hoy) {
            // Construimos el enlace para cancelar inscripción
            $url = $urlGenerator->generate('intranet_forpas_participante_inscripcion_cancelar', ['id' => $this->getId()]);
            return sprintf('<b>Inscrito/a en esta edición</b><br><a href="%s" onclick="return confirm(\'¿Estás seguro de que deseas cancelar tu inscripción?\');">Cancelar inscripción</a>', $url);
        }

        // Caso 3: Cambiar inscripción
        if ($edicionInscrita && $edicionInscrita->getEdicion() !== $this &&
            $edicionInscrita->getEdicion()->getFechaInicio() >= $hoy &&
            $this->getFechaInicio() >= $hoy &&
            $this->getPlazasLibres() > 0
        ) {
            // Construimos el enlace para cambiar inscripción
            $url = $urlGenerator->generate('intranet_forpas_participante_inscripcion_cambiar', ['id' => $this->getId()]);
            return sprintf('<a href="%s" onclick="return confirm(\'¿Estás seguro de que deseas cambiar tu inscripción a esta edición?\');">Cambiar a esta edición</a>', $url);
        }

        // Caso 4: Realizar inscripción
        if (!$edicionInscrita && $this->getFechaInicio() >= $hoy && $this->getPlazasLibres() > 0) {
            // Construimos el enlace para realizar inscripción
            $url = $urlGenerator->generate('intranet_forpas_participante_inscripcion_realizar', ['id' => $this->getId()]);
            return sprintf('<a href="%s" onclick="return confirm(\'¿Estás seguro de que deseas inscribirte en esta edición?\');">Solicitar inscripción</a>', $url);
        }

        // Caso 5: información de inscripción
        if ($edicionInscrita && $edicionInscrita->getEdicion() === $this && $this->getFechaInicio() < $hoy) {
            return '<b>Inscrito/a en esta edición</b>';
        }

        // Caso 6: Inscripción cerrada
        return 'Inscripción cerrada';
    }


    public function getCurso(): ?Curso
    {
        return $this->curso;
    }

    public function setCurso(?Curso $curso): static
    {
        $this->curso = $curso;

        return $this;
    }

    /**
     * @return Collection<int, ParticipanteEdicion>
     */
    public function getParticipantesEdicion(): Collection
    {
        return $this->participantesEdicion;
    }

    public function addParticipantesEdicion(ParticipanteEdicion $participanteEdicion): static
    {
        if (!$this->participantesEdicion->contains($participanteEdicion)) {
            $this->participantesEdicion->add($participanteEdicion);
        }

        return $this;
    }

    public function removeParticipantesEdicion(ParticipanteEdicion $participanteEdicion): static
    {
        if ($this->participantesEdicion->removeElement($participanteEdicion)) {
            if ($participanteEdicion->getEdicion() === $this) {
                $participanteEdicion->setEdicion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FormadorEdicion>
     */
    public function getFormadoresEdicion(): Collection
    {
        return $this->formadoresEdicion;
    }

    public function addFormadoresEdicion(FormadorEdicion $formadorEdicion): static
    {
        if (!$this->formadoresEdicion->contains($formadorEdicion)) {
            $this->formadoresEdicion->add($formadorEdicion);
        }

        return $this;
    }

    public function removeFormadoresEdicion(FormadorEdicion $formadorEdicion): static
    {
        if ($this->formadoresEdicion->removeElement($formadorEdicion)) {
            if ($formadorEdicion->getEdicion() === $this) {
                $formadorEdicion->setEdicion(null);
            }
        }

        return $this;
    }
}
