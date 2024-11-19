<?php

namespace App\Entity\Forpas;

use App\Repository\Forpas\FormadorEdicionRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormadorEdicionRepository::class)]
class FormadorEdicion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'formadorEdiciones')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Formador $formador = null;

    #[ORM\ManyToOne(inversedBy: 'formadoresEdicion')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Edicion $edicion = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $horas_impartidas = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 7, scale: 2, nullable: true)]
    private ?string $retrib_prevista = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 7, scale: 2, nullable: true)]
    private ?string $retrib_ejecutada = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observaciones = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $fedap = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $evaluacion = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $hoja_firma = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $datos_banco = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $incompatibilidad = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $grabado_sorolla = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $sin_coste = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $coincide_turno = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coincide_turno_observaciones = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $control_personal_enviado = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $control_personal_recibido = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEdicion(): ?Edicion
    {
        return $this->edicion;
    }

    public function setEdicion(?Edicion $edicion): static
    {
        $this->edicion = $edicion;

        return $this;
    }

    public function getHorasImpartidas(): ?string
    {
        return $this->horas_impartidas;
    }

    public function setHorasImpartidas(string $horas_impartidas): static
    {
        $this->horas_impartidas = $horas_impartidas;

        return $this;
    }

    public function getRetribPrevista(): ?string
    {
        return $this->retrib_prevista;
    }

    public function setRetribPrevista(string $retrib_prevista): static
    {
        $this->retrib_prevista = $retrib_prevista;

        return $this;
    }

    public function getRetribEjecutada(): ?string
    {
        return $this->retrib_ejecutada;
    }

    public function setRetribEjecutada(?string $retrib_ejecutada): static
    {
        $this->retrib_ejecutada = $retrib_ejecutada;

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

    public function isFedap(): ?bool
    {
        return $this->fedap;
    }

    public function setFedap(bool $fedap): static
    {
        $this->fedap = $fedap;

        return $this;
    }

    public function isEvaluacion(): ?bool
    {
        return $this->evaluacion;
    }

    public function setEvaluacion(bool $evaluacion): static
    {
        $this->evaluacion = $evaluacion;

        return $this;
    }

    public function getHojaFirma(): ?DateTimeInterface
    {
        return $this->hoja_firma;
    }

    public function setHojaFirma(?DateTimeInterface $hoja_firma): static
    {
        $this->hoja_firma = $hoja_firma;

        return $this;
    }

    public function getDatosBanco(): ?DateTimeInterface
    {
        return $this->datos_banco;
    }

    public function setDatosBanco(?DateTimeInterface $datos_banco): static
    {
        $this->datos_banco = $datos_banco;

        return $this;
    }

    public function getIncompatibilidad(): ?DateTimeInterface
    {
        return $this->incompatibilidad;
    }

    public function setIncompatibilidad(?DateTimeInterface $incompatibilidad): static
    {
        $this->incompatibilidad = $incompatibilidad;

        return $this;
    }

    public function getGrabadoSorolla(): ?string
    {
        return $this->grabado_sorolla;
    }

    public function setGrabadoSorolla(?string $grabado_sorolla): static
    {
        $this->grabado_sorolla = $grabado_sorolla;

        return $this;
    }

    public function isSinCoste(): ?bool
    {
        return $this->sin_coste;
    }

    public function setSinCoste(bool $sin_coste): static
    {
        $this->sin_coste = $sin_coste;

        return $this;
    }

    public function getCoincideTurno(): ?int
    {
        return $this->coincide_turno;
    }

    public function setCoincideTurno(?int $coincide_turno): static
    {
        $this->coincide_turno = $coincide_turno;

        return $this;
    }

    public function getCoincideTurnoObservaciones(): ?string
    {
        return $this->coincide_turno_observaciones;
    }

    public function setCoincideTurnoObservaciones(?string $coincide_turno_observaciones): static
    {
        $this->coincide_turno_observaciones = $coincide_turno_observaciones;

        return $this;
    }

    public function getControlPersonalEnviado(): ?DateTimeInterface
    {
        return $this->control_personal_enviado;
    }

    public function setControlPersonalEnviado(?DateTimeInterface $control_personal_enviado): static
    {
        $this->control_personal_enviado = $control_personal_enviado;

        return $this;
    }

    public function getControlPersonalRecibido(): ?DateTimeInterface
    {
        return $this->control_personal_recibido;
    }

    public function setControlPersonalRecibido(?DateTimeInterface $control_personal_recibido): static
    {
        $this->control_personal_recibido = $control_personal_recibido;

        return $this;
    }
}
