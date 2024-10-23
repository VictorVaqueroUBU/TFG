<?php

namespace App\Entity;

use App\Repository\CursoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CursoRepository::class)]
class Curso
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $codigo_curso = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre_curso = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $horas = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $objetivos = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contenidos = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $destinatarios = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $requisitos = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $justificacion = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coordinador = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $participantes_edicion = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $ediciones_estimadas = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $plazo_solicitud = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observaciones = null;

    #[ORM\Column]
    private ?bool $visible_web = null;

    //#[ORM\Column]
    //private ?int $id_programa = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $horas_virtuales = null;

    #[ORM\Column]
    private ?bool $calificable = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodigoCurso(): ?string
    {
        return $this->codigo_curso;
    }

    public function setCodigoCurso(string $codigo_curso): static
    {
        $this->codigo_curso = $codigo_curso;

        return $this;
    }

    public function getNombreCurso(): ?string
    {
        return $this->nombre_curso;
    }

    public function setNombreCurso(string $nombre_curso): static
    {
        $this->nombre_curso = $nombre_curso;

        return $this;
    }

    public function getHoras(): ?int
    {
        return $this->horas;
    }

    public function setHoras(int $horas): static
    {
        $this->horas = $horas;

        return $this;
    }

    public function getObjetivos(): ?string
    {
        return $this->objetivos;
    }

    public function setObjetivos(?string $objetivos): static
    {
        $this->objetivos = $objetivos;

        return $this;
    }

    public function getContenidos(): ?string
    {
        return $this->contenidos;
    }

    public function setContenidos(?string $contenidos): static
    {
        $this->contenidos = $contenidos;

        return $this;
    }

    public function getDestinatarios(): ?string
    {
        return $this->destinatarios;
    }

    public function setDestinatarios(?string $destinatarios): static
    {
        $this->destinatarios = $destinatarios;

        return $this;
    }

    public function getRequisitos(): ?string
    {
        return $this->requisitos;
    }

    public function setRequisitos(?string $requisitos): static
    {
        $this->requisitos = $requisitos;

        return $this;
    }

    public function getJustificacion(): ?string
    {
        return $this->justificacion;
    }

    public function setJustificacion(?string $justificacion): static
    {
        $this->justificacion = $justificacion;

        return $this;
    }

    public function getCoordinador(): ?string
    {
        return $this->coordinador;
    }

    public function setCoordinador(?string $coordinador): static
    {
        $this->coordinador = $coordinador;

        return $this;
    }

    public function getParticipantesEdicion(): ?int
    {
        return $this->participantes_edicion;
    }

    public function setParticipantesEdicion(?int $participantes_edicion): static
    {
        $this->participantes_edicion = $participantes_edicion;

        return $this;
    }

    public function getEdicionesEstimadas(): ?int
    {
        return $this->ediciones_estimadas;
    }

    public function setEdicionesEstimadas(?int $ediciones_estimadas): static
    {
        $this->ediciones_estimadas = $ediciones_estimadas;

        return $this;
    }

    public function getPlazoSolicitud(): ?string
    {
        return $this->plazo_solicitud;
    }

    public function setPlazoSolicitud(?string $plazo_solicitud): static
    {
        $this->plazo_solicitud = $plazo_solicitud;

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

    public function isVisibleWeb(): ?bool
    {
        return $this->visible_web;
    }

    public function setVisibleWeb(bool $visible_web): static
    {
        $this->visible_web = $visible_web;

        return $this;
    }

    public function getIdPrograma(): ?int
    {
        return $this->id_programa;
    }

    public function setIdPrograma(int $id_programa): static
    {
        $this->id_programa = $id_programa;

        return $this;
    }

    public function getHorasVirtuales(): ?int
    {
        return $this->horas_virtuales;
    }

    public function setHorasVirtuales(int $horas_virtuales): static
    {
        $this->horas_virtuales = $horas_virtuales;

        return $this;
    }

    public function isCalificable(): ?bool
    {
        return $this->calificable;
    }

    public function setCalificable(bool $calificable): static
    {
        $this->calificable = $calificable;

        return $this;
    }
}
