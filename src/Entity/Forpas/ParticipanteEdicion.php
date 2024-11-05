<?php

namespace App\Entity\Forpas;

use App\Repository\Forpas\ParticipanteEdicionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipanteEdicionRepository::class)]
class ParticipanteEdicion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'participanteEdiciones')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participante $participante = null;

    #[ORM\ManyToOne(inversedBy: 'participantesEdicion')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Edicion $edicion = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fecha_solicitud = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $baja_justificada = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2, nullable: true)]
    private ?string $prueba_final = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $certificado = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $libro = null;

    #[ORM\Column(nullable: true)]
    private ?int $numero_titulo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observaciones = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $apto = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $direccion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParticipante(): ?Participante
    {
        return $this->participante;
    }

    public function setParticipante(?Participante $participante): static
    {
        $this->participante = $participante;

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

    public function getFechaSolicitud(): ?\DateTimeInterface
    {
        return $this->fecha_solicitud;
    }

    public function setFechaSolicitud(\DateTimeInterface $fecha_solicitud): static
    {
        $this->fecha_solicitud = $fecha_solicitud;

        return $this;
    }

    public function getBajaJustificada(): ?\DateTimeInterface
    {
        return $this->baja_justificada;
    }

    public function setBajaJustificada(?\DateTimeInterface $baja_justificada): static
    {
        $this->baja_justificada = $baja_justificada;

        return $this;
    }

    public function getPruebaFinal(): ?string
    {
        return $this->prueba_final;
    }

    public function setPruebaFinal(?string $prueba_final): static
    {
        $this->prueba_final = $prueba_final;

        return $this;
    }

    public function getCertificado(): ?string
    {
        return $this->certificado;
    }

    public function setCertificado(?string $certificado): static
    {
        $this->certificado = $certificado;

        return $this;
    }

    public function getLibro(): ?int
    {
        return $this->libro;
    }

    public function setLibro(?int $libro): static
    {
        $this->libro = $libro;

        return $this;
    }

    public function getNumeroTitulo(): ?int
    {
        return $this->numero_titulo;
    }

    public function setNumeroTitulo(?int $numero_titulo): static
    {
        $this->numero_titulo = $numero_titulo;

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

    public function getApto(): ?int
    {
        return $this->apto;
    }

    public function setApto(?int $apto): static
    {
        $this->apto = $apto;

        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(?string $direccion): static
    {
        $this->direccion = $direccion;

        return $this;
    }
}
