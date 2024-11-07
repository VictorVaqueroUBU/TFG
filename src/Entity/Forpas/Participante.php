<?php

namespace App\Entity\Forpas;

use App\Repository\Forpas\ParticipanteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipanteRepository::class)]
class Participante
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 9)]
    private ?string $nif = null;

    #[ORM\Column(length: 50)]
    private ?string $apellidos = null;

    #[ORM\Column(length: 50)]
    private ?string $nombre = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $descripcion_cce = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $codigo_cce = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $grupo = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $nivel = null;

    #[ORM\Column(length: 75, nullable: true)]
    private ?string $puesto_trabajo = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $subunidad = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $unidad = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $centro_destino = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $t_r_juridico = null;

    #[ORM\Column(length: 75, nullable: true)]
    private ?string $situacion_admin = null;

    #[ORM\Column(length: 8, nullable: true)]
    private ?string $codigo_plaza = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $telefono_trabajo = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $correo_aux = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $codigo_rpt = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $organizacion = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $turno = null;

    #[ORM\Column(length: 9, nullable: true)]
    private ?string $telefono_particular = null;

    #[ORM\Column(length: 9, nullable: true)]
    private ?string $telefono_movil = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $fecha_nacimiento = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $titulacion_nivel = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $titulacion_fecha = null;

    #[ORM\Column(length: 75, nullable: true)]
    private ?string $titulacion = null;

    #[ORM\Column(length: 8, nullable: true)]
    private ?string $dni_sin_letra = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $uvus = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $sexo = null;

    /**
     * @var Collection<int, ParticipanteEdicion>
     */
    #[ORM\OneToMany(targetEntity: ParticipanteEdicion::class, mappedBy: 'participante', orphanRemoval: true)]
    private Collection $participanteEdiciones;

    public function __construct()
    {
        $this->participanteEdiciones = new ArrayCollection();
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

    public function getDescripcionCce(): ?string
    {
        return $this->descripcion_cce;
    }

    public function setDescripcionCce(?string $descripcion_cce): static
    {
        $this->descripcion_cce = $descripcion_cce;

        return $this;
    }

    public function getCodigoCce(): ?string
    {
        return $this->codigo_cce;
    }

    public function setCodigoCce(?string $codigo_cce): static
    {
        $this->codigo_cce = $codigo_cce;

        return $this;
    }

    public function getGrupo(): ?string
    {
        return $this->grupo;
    }

    public function setGrupo(?string $grupo): static
    {
        $this->grupo = $grupo;

        return $this;
    }

    public function getNivel(): ?int
    {
        return $this->nivel;
    }

    public function setNivel(?int $nivel): static
    {
        $this->nivel = $nivel;

        return $this;
    }

    public function getPuestoTrabajo(): ?string
    {
        return $this->puesto_trabajo;
    }

    public function setPuestoTrabajo(?string $puesto_trabajo): static
    {
        $this->puesto_trabajo = $puesto_trabajo;

        return $this;
    }

    public function getSubunidad(): ?string
    {
        return $this->subunidad;
    }

    public function setSubunidad(?string $subunidad): static
    {
        $this->subunidad = $subunidad;

        return $this;
    }

    public function getUnidad(): ?string
    {
        return $this->unidad;
    }

    public function setUnidad(?string $unidad): static
    {
        $this->unidad = $unidad;

        return $this;
    }

    public function getCentroDestino(): ?string
    {
        return $this->centro_destino;
    }

    public function setCentroDestino(?string $centro_destino): static
    {
        $this->centro_destino = $centro_destino;

        return $this;
    }

    public function getTRJuridico(): ?string
    {
        return $this->t_r_juridico;
    }

    public function setTRJuridico(?string $t_r_juridico): static
    {
        $this->t_r_juridico = $t_r_juridico;

        return $this;
    }

    public function getSituacionAdmin(): ?string
    {
        return $this->situacion_admin;
    }

    public function setSituacionAdmin(?string $situacion_admin): static
    {
        $this->situacion_admin = $situacion_admin;

        return $this;
    }

    public function getCodigoPlaza(): ?string
    {
        return $this->codigo_plaza;
    }

    public function setCodigoPlaza(?string $codigo_plaza): static
    {
        $this->codigo_plaza = $codigo_plaza;

        return $this;
    }

    public function getTelefonoTrabajo(): ?string
    {
        return $this->telefono_trabajo;
    }

    public function setTelefonoTrabajo(?string $telefono_trabajo): static
    {
        $this->telefono_trabajo = $telefono_trabajo;

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

    public function getCodigoRpt(): ?string
    {
        return $this->codigo_rpt;
    }

    public function setCodigoRpt(?string $codigo_rpt): static
    {
        $this->codigo_rpt = $codigo_rpt;

        return $this;
    }

    public function getOrganizacion(): ?string
    {
        return $this->organizacion;
    }

    public function setOrganizacion(?string $organizacion): static
    {
        $this->organizacion = $organizacion;

        return $this;
    }

    public function getTurno(): ?string
    {
        return $this->turno;
    }

    public function setTurno(?string $turno): static
    {
        $this->turno = $turno;

        return $this;
    }

    public function getTelefonoParticular(): ?string
    {
        return $this->telefono_particular;
    }

    public function setTelefonoParticular(?string $telefono_particular): static
    {
        $this->telefono_particular = $telefono_particular;

        return $this;
    }

    public function getTelefonoMovil(): ?string
    {
        return $this->telefono_movil;
    }

    public function setTelefonoMovil(?string $telefono_movil): static
    {
        $this->telefono_movil = $telefono_movil;

        return $this;
    }

    public function getFechaNacimiento(): ?\DateTimeInterface
    {
        return $this->fecha_nacimiento;
    }

    public function setFechaNacimiento(?\DateTimeInterface $fecha_nacimiento): static
    {
        $this->fecha_nacimiento = $fecha_nacimiento;

        return $this;
    }

    public function getTitulacionNivel(): ?int
    {
        return $this->titulacion_nivel;
    }

    public function setTitulacionNivel(?int $titulacion_nivel): static
    {
        $this->titulacion_nivel = $titulacion_nivel;

        return $this;
    }

    public function getTitulacionFecha(): ?\DateTimeInterface
    {
        return $this->titulacion_fecha;
    }

    public function setTitulacionFecha(?\DateTimeInterface $titulacion_fecha): static
    {
        $this->titulacion_fecha = $titulacion_fecha;

        return $this;
    }

    public function getTitulacion(): ?string
    {
        return $this->titulacion;
    }

    public function setTitulacion(?string $titulacion): static
    {
        $this->titulacion = $titulacion;

        return $this;
    }

    public function getDniSinLetra(): ?string
    {
        return $this->dni_sin_letra;
    }

    public function setDniSinLetra(?string $dni_sin_letra): static
    {
        $this->dni_sin_letra = $dni_sin_letra;

        return $this;
    }

    public function getUvus(): ?string
    {
        return $this->uvus;
    }

    public function setUvus(?string $uvus): static
    {
        $this->uvus = $uvus;

        return $this;
    }

    public function getSexo(): ?string
    {
        return $this->sexo;
    }

    public function setSexo(?string $sexo): static
    {
        $this->sexo = $sexo;

        return $this;
    }

    /**
     * @return Collection<int, ParticipanteEdicion>
     */
    public function getParticipanteEdiciones(): Collection
    {
        return $this->participanteEdiciones;
    }

    public function addParticipanteEdiciones(ParticipanteEdicion $participanteEdicion): static
    {
        if (!$this->participanteEdiciones->contains($participanteEdicion)) {
            $this->participanteEdiciones->add($participanteEdicion);
            $participanteEdicion->setParticipante($this);
        }

        return $this;
    }

    public function removeParticipanteEdiciones(ParticipanteEdicion $participanteEdicion): static
    {
        if ($this->participanteEdiciones->removeElement($participanteEdicion)) {
            // set the owning side to null (unless already changed)
            if ($participanteEdicion->getParticipante() === $this) {
                $participanteEdicion->setParticipante(null);
            }
        }

        return $this;
    }
}
