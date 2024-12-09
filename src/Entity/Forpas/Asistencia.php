<?php

namespace App\Entity\Forpas;

use App\Repository\Forpas\AsistenciaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AsistenciaRepository::class)]
class Asistencia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $asiste = null;

    #[ORM\Column]
    private ?bool $justifica = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observaciones = null;

    #[ORM\ManyToOne(inversedBy: 'asistencias')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participante $participante = null;

    #[ORM\ManyToOne(inversedBy: 'asistencias')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Formador $formador = null;

    #[ORM\ManyToOne(inversedBy: 'asistencias')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sesion $sesion = null;

    private ?string $estado = null;

    public function getEstado(): ?string
    {
        if ($this->isAsiste()) {
            return 'asiste';
        } elseif ($this->isJustifica()) {
            return 'justifica';
        } else {
            return 'ninguno';
        }
    }

    public function setEstado(string $estado): self
    {
        switch ($estado) {
            case 'asiste':
                $this->setAsiste(true)->setJustifica(false);
                break;
            case 'justifica':
                $this->setAsiste(false)->setJustifica(true);
                break;
            default:
                $this->setAsiste(false)->setJustifica(false);
                break;
        }

        $this->estado = $estado;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isAsiste(): ?bool
    {
        return $this->asiste;
    }

    public function setAsiste(bool $asiste): static
    {
        $this->asiste = $asiste;

        return $this;
    }

    public function isJustifica(): ?bool
    {
        return $this->justifica;
    }

    public function setJustifica(bool $justifica): static
    {
        $this->justifica = $justifica;

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

    public function getParticipante(): ?Participante
    {
        return $this->participante;
    }

    public function setParticipante(?Participante $participante): static
    {
        $this->participante = $participante;

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

    public function getSesion(): ?Sesion
    {
        return $this->sesion;
    }

    public function setSesion(?Sesion $sesion): static
    {
        $this->sesion = $sesion;

        return $this;
    }
}
