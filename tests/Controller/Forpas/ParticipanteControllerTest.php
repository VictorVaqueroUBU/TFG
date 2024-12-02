<?php

namespace App\Tests\Controller\Forpas;

use App\Entity\Forpas\Curso;
use App\Entity\Forpas\Edicion;
use App\Entity\Forpas\Formador;
use App\Entity\Forpas\Participante;
use App\Entity\Sistema\Usuario;
use DateTime;
use Doctrine\ORM\EntityRepository;

final class ParticipanteControllerTest extends BaseControllerTest
{
    /**
     * @var EntityRepository<Participante>
     */
    private EntityRepository $repository;
    private string $path = '/intranet/forpas/gestor/participante/';
    protected function setUp(): void
    {
        parent::setUp(); // Llama al setUp de la clase base

        // Limpiamos datos de ParticipanteEdicion, Edicion, Curso y Participante
        $repositories = [
            Edicion::class,
            Curso::class,
            Participante::class,
            Formador::class,
            Usuario::class,
        ];

        foreach ($repositories as $repositoryClass) {
            $repository = $this->manager->getRepository($repositoryClass);
            foreach ($repository->findAll() as $object) {
                $this->manager->remove($object);
            }
        }

        $this->manager->flush();

        // Creamos y autenticamos un usuario por defecto
        $this->client->loginUser($this->createUserWithRole('ROLE_ADMIN'));
        // Asignamos el repositorio de Participante para los tests
        $this->repository = $this->manager->getRepository(Participante::class);
    }
    public function testIndex(): void
    {
        $this->client->followRedirects();
        $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Listado de Participantes');
    }
    public function testNew(): void
    {
        // Caso 1: Creamos un Participante desde un Formador válido
        $usuario = $this->createUserWithRole('ROLE_USER');
        $formador = new Formador();
        $formador->setNif('12345678A');
        $formador->setNombre('Juan');
        $formador->setApellidos('Pérez');
        $formador->setUsuario($usuario);
        $formador->setOrganizacion('Organización de prueba');

        $this->manager->persist($formador);
        $this->manager->flush();

        $this->client->request('GET', $this->path . 'new/' . $formador->getId());
        $participante = $this->repository->findOneBy(['usuario' => $usuario]);
        $this->assertNotNull($participante, 'El Participante debería haber sido creado.');
        $this->assertSame('12345678A', $participante->getNif(), 'El NIF debería coincidir.');
        $this->assertResponseRedirects('/intranet/forpas/gestor/participante/', 303);

        // Caso 2: Intentamos crear un Participante cuando ya existe uno asociado al Usuario
        $this->client->request('GET', $this->path . 'new/' . $formador->getId());

        // Verificamos que no se crea un duplicado
        $participantes = $this->repository->findBy(['usuario' => $usuario]);
        $this->assertCount(1, $participantes, 'No debería haber duplicados de Participante para el mismo Usuario.');
    }
    public function testShow(): void
    {
        // Creamos un usuario para asociar al formador
        $usuario = $this->createUserWithRole('ROLE_USER');

        $fixture = new Participante();
        $fixture->setNif('My Title');
        $fixture->setApellidos('My Title');
        $fixture->setNombre('My Title');
        $fixture->setDescripcionCce('My Title');
        $fixture->setCodigoCce('Title');
        $fixture->setGrupo('A1');
        $fixture->setNivel(27);
        $fixture->setPuestoTrabajo('My Title');
        $fixture->setSubunidad('My Title');
        $fixture->setUnidad('My Title');
        $fixture->setCentroDestino('My Title');
        $fixture->setTRJuridico('FC');
        $fixture->setSituacionAdmin('My Title');
        $fixture->setCodigoPlaza('My Title');
        $fixture->setTelefonoTrabajo('My Title');
        $fixture->setCorreoAux('My Title');
        $fixture->setCodigoRpt('My Title');
        $fixture->setOrganizacion('My Title');
        $fixture->setTurno('My Title');
        $fixture->setTelefonoParticular('My Title');
        $fixture->setTelefonoMovil('My Title');
        $fixture->setFechaNacimiento(new DateTime('2024-01-01'));
        $fixture->setTitulacionNivel(1);
        $fixture->setTitulacionFecha(new DateTime('2024-01-01'));
        $fixture->setTitulacion('My Title');
        $fixture->setDniSinLetra('My Title');
        $fixture->setSexo('V');
        $fixture->setUsuario($usuario);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Datos del Participante');
    }
    public function testEdit(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');

        $fixture = new Participante();
        $fixture->setNif('Value');
        $fixture->setApellidos('Value');
        $fixture->setNombre('Value');
        $fixture->setDescripcionCce('Value');
        $fixture->setCodigoCce('Value');
        $fixture->setGrupo('A1');
        $fixture->setNivel(27);
        $fixture->setPuestoTrabajo('Value');
        $fixture->setSubunidad('Value');
        $fixture->setUnidad('Value');
        $fixture->setCentroDestino('Value');
        $fixture->setTRJuridico('FC');
        $fixture->setSituacionAdmin('Value');
        $fixture->setCodigoPlaza('Value');
        $fixture->setTelefonoTrabajo('Value');
        $fixture->setCorreoAux('Value');
        $fixture->setCodigoRpt('Value');
        $fixture->setOrganizacion('Value');
        $fixture->setTurno('Value');
        $fixture->setTelefonoParticular('Value');
        $fixture->setTelefonoMovil('Value');
        $fixture->setFechaNacimiento(new DateTime('2024-01-01'));
        $fixture->setTitulacionNivel(1);
        $fixture->setTitulacionFecha(new DateTime('2024-01-01'));
        $fixture->setTitulacion('Value');
        $fixture->setDniSinLetra('Value');
        $fixture->setSexo('V');
        $fixture->setUsuario($usuario);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Actualizar', [
            'participante[nif]' => 'Some',
            'participante[apellidos]' => 'Something New',
            'participante[nombre]' => 'Something New',
            'participante[descripcion_cce]' => 'Something New',
            'participante[codigo_cce]' => 'Some',
            'participante[grupo]' => 'A1',
            'participante[nivel]' => 27,
            'participante[puesto_trabajo]' => 'Something New',
            'participante[subunidad]' => 'Something New',
            'participante[unidad]' => 'Something New',
            'participante[centro_destino]' => 'Something New',
            'participante[t_r_juridico]' => 'FC',
            'participante[situacion_admin]' => 'Something New',
            'participante[codigo_plaza]' => 'Some',
            'participante[telefono_trabajo]' => 'Something New',
            'participante[correo_aux]' => 'Something New',
            'participante[codigo_rpt]' => 'Something New',
            'participante[organizacion]' => 'Something New',
            'participante[turno]' => 'Something New',
            'participante[telefono_particular]' => 'Something',
            'participante[telefono_movil]' => 'Something',
            'participante[fecha_nacimiento]' => '2024-01-01',
            'participante[titulacion_nivel]' => 1,
            'participante[titulacion_fecha]' => '2024-01-01',
            'participante[titulacion]' => 'Something New',
            'participante[dni_sin_letra]' => 'Some',
            'participante[sexo]' => 'V',
        ]);

        self::assertResponseRedirects('/intranet/forpas/gestor/participante/', 303);

        $fixture = $this->repository->findAll();

        self::assertSame('Some', $fixture[0]->getNif());
        self::assertSame('Something New', $fixture[0]->getApellidos());
        self::assertSame('Something New', $fixture[0]->getNombre());
        self::assertSame('Something New', $fixture[0]->getDescripcionCce());
        self::assertSame('Some', $fixture[0]->getCodigoCce());
        self::assertSame('A1', $fixture[0]->getGrupo());
        self::assertSame(27, $fixture[0]->getNivel());
        self::assertSame('Something New', $fixture[0]->getPuestoTrabajo());
        self::assertSame('Something New', $fixture[0]->getSubunidad());
        self::assertSame('Something New', $fixture[0]->getUnidad());
        self::assertSame('Something New', $fixture[0]->getCentroDestino());
        self::assertSame('FC', $fixture[0]->getTRJuridico());
        self::assertSame('Something New', $fixture[0]->getSituacionAdmin());
        self::assertSame('Some', $fixture[0]->getCodigoPlaza());
        self::assertSame('Something New', $fixture[0]->getTelefonoTrabajo());
        self::assertSame('Something New', $fixture[0]->getCorreoAux());
        self::assertSame('Something New', $fixture[0]->getCodigoRpt());
        self::assertSame('Something New', $fixture[0]->getOrganizacion());
        self::assertSame('Something New', $fixture[0]->getTurno());
        self::assertSame('Something', $fixture[0]->getTelefonoParticular());
        self::assertSame('Something', $fixture[0]->getTelefonoMovil());
        self::assertEquals(new DateTime('2024-01-01'), $fixture[0]->getFechaNacimiento());
        self::assertSame(1, $fixture[0]->getTitulacionNivel());
        self::assertEquals(new DateTime('2024-01-01'), $fixture[0]->getTitulacionFecha());
        self::assertSame('Something New', $fixture[0]->getTitulacion());
        self::assertSame('Some', $fixture[0]->getDniSinLetra());
        self::assertSame('V', $fixture[0]->getSexo());
    }
    public function testRemove(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');

        $fixture = new Participante();
        $fixture->setNif('Value');
        $fixture->setApellidos('Value');
        $fixture->setNombre('Value');
        $fixture->setDescripcionCce('Value');
        $fixture->setCodigoCce('Value');
        $fixture->setGrupo('A1');
        $fixture->setNivel(27);
        $fixture->setPuestoTrabajo('Value');
        $fixture->setSubunidad('Value');
        $fixture->setUnidad('Value');
        $fixture->setCentroDestino('Value');
        $fixture->setTRJuridico('FC');
        $fixture->setSituacionAdmin('Value');
        $fixture->setCodigoPlaza('Value');
        $fixture->setTelefonoTrabajo('Value');
        $fixture->setCorreoAux('Value');
        $fixture->setCodigoRpt('Value');
        $fixture->setOrganizacion('Value');
        $fixture->setTurno('Value');
        $fixture->setTelefonoParticular('Value');
        $fixture->setTelefonoMovil('Value');
        $fixture->setFechaNacimiento(new DateTime('2024-01-01'));
        $fixture->setTitulacionNivel(1);
        $fixture->setTitulacionFecha(new DateTime('2024-01-01'));
        $fixture->setTitulacion('Value');
        $fixture->setDniSinLetra('Value');
        $fixture->setSexo('V');
        $fixture->setUsuario($usuario);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Eliminar');

        self::assertResponseRedirects('/intranet/forpas/gestor/participante/', 303);
        self::assertSame(0, $this->repository->count([]));
    }
    public function testAppend(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');

        // Creamos una entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24101');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(0);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos una entidad Edicion asociada al Curso
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24101/01');
        $edicion->setEstado(0);
        $edicion->setSesiones(2);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);
        $curso->addEdiciones($edicion);
        $this->manager->persist($edicion);

        // Creamos una entidad Participante
        $participante = new Participante();
        $participante->setNif('12345678A');
        $participante->setNombre('John');
        $participante->setApellidos('Doe');
        $participante->setUnidad('Unidad');
        $participante->setUsuario($usuario);
        $this->manager->persist($participante);

        // Persistimos todos los datos en la base de datos
        $this->manager->flush();

        // Obtenemos el ID generado automáticamente para la Edicion
        $id = $edicion->getId();

        // Realizamos la solicitud al controlador
        $this->client->request('GET', "/intranet/forpas/gestor/participante/append/$id");

        // Verificamos el código de respuesta HTTP
        self::assertResponseStatusCodeSame(200);

        // Verificamos que la vista contiene los datos de los participantes disponibles
        self::assertSelectorTextContains(
            '#datosParticipantesSeleccionables tbody tr:first-child td:nth-child(1)',
            '12345678A'
        );
    }
}
