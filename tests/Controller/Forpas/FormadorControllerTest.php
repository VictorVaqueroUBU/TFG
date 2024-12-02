<?php

namespace App\Tests\Controller\Forpas;

use App\Entity\Forpas\Curso;
use App\Entity\Forpas\Edicion;
use App\Entity\Forpas\Formador;
use App\Entity\Forpas\Participante;
use App\Entity\Sistema\Usuario;
use Doctrine\ORM\EntityRepository;

final class FormadorControllerTest extends BaseControllerTest
{
    /**
     * @var EntityRepository<Formador>
     */
    private EntityRepository $repository;
    private string $path = '/intranet/forpas/gestor/formador/';
    protected function setUp(): void
    {
        parent::setUp(); // Llama al setUp de la clase base

        // Limpiamos datos de Edición, Curso y Formador
        $repositories = [
            Participante::class,
            Formador::class,
            Edicion::class,
            Curso::class,
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
        // Asignamos el repositorio de Formador para los tests
        $this->repository = $this->manager->getRepository(Formador::class);
    }
    public function testIndex(): void
    {
        $this->client->followRedirects();
        $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Listado de Formadores');
    }
    public function testNew(): void
    {
        // Caso 1: Creamos un Formador desde un Participante válido
        $usuario = $this->createUserWithRole('ROLE_USER');
        $participante = new Participante();
        $participante->setNif('12345678A');
        $participante->setNombre('Juan');
        $participante->setApellidos('Pérez');
        $participante->setUsuario($usuario);
        $participante->setOrganizacion('Organización de prueba');

        $this->manager->persist($participante);
        $this->manager->flush();

        $this->client->request('GET', $this->path . 'new/' . $participante->getId());
        $formador = $this->repository->findOneBy(['usuario' => $usuario]);
        $this->assertNotNull($formador, 'El Formador debería haber sido creado.');
        $this->assertSame('12345678A', $formador->getNif(), 'El NIF debería coincidir.');
        $this->assertResponseRedirects('/intranet/forpas/gestor/formador/', 303);

        // Caso 2: Intentamos crear un Formador cuando ya existe uno asociado al Usuario
        $this->client->request('GET', $this->path . 'new/' . $participante->getId());

        // Verificamos que no se crea un duplicado
        $formadores = $this->repository->findBy(['usuario' => $usuario]);
        $this->assertCount(1, $formadores, 'No debería haber duplicados de Formador para el mismo Usuario.');
    }
    public function testShow(): void
    {
        // Creamos un usuario para asociar al formador
        $usuario = $this->createUserWithRole('ROLE_USER');

        $fixture = new Formador();
        $fixture->setNif('My Title');
        $fixture->setApellidos('My Title');
        $fixture->setNombre('My Title');
        $fixture->setOrganizacion('My Title');
        $fixture->setCorreoAux('My Title');
        $fixture->setTelefono('My Title');
        $fixture->setObservaciones('My Title');
        $fixture->setFormadorRJ(1);
        $fixture->setUsuario($usuario);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Datos del Formador');
    }
    public function testEdit(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');

        $fixture = new Formador();
        $fixture->setNif('Value');
        $fixture->setApellidos('Value');
        $fixture->setNombre('Value');
        $fixture->setOrganizacion('Value');
        $fixture->setCorreoAux('Value');
        $fixture->setTelefono('Value');
        $fixture->setObservaciones('Value');
        $fixture->setFormadorRJ(1);
        $fixture->setUsuario($usuario);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Actualizar', [
            'formador[nif]' => 'Something',
            'formador[apellidos]' => 'Something New',
            'formador[nombre]' => 'Something New',
            'formador[organizacion]' => 'Something New',
            'formador[correoAux]' => 'Something New',
            'formador[telefono]' => 'Something New',
            'formador[observaciones]' => 'Something New',
            'formador[formadorRJ]' => 1,
        ]);

        self::assertResponseRedirects($this->path);
        $fixture = $this->repository->findAll();

        self::assertSame('Something', $fixture[0]->getNif());
        self::assertSame('Something New', $fixture[0]->getApellidos());
        self::assertSame('Something New', $fixture[0]->getNombre());
        self::assertSame('Something New', $fixture[0]->getOrganizacion());
        self::assertSame('Something New', $fixture[0]->getCorreoAux());
        self::assertSame('Something New', $fixture[0]->getTelefono());
        self::assertSame('Something New', $fixture[0]->getObservaciones());
        self::assertSame(1, $fixture[0]->getFormadorRJ());
    }
    public function testRemove(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');

        $fixture = new Formador();
        $fixture->setNif('Value2');
        $fixture->setApellidos('Value2');
        $fixture->setNombre('Value2');
        $fixture->setOrganizacion('Value2');
        $fixture->setCorreoAux('Value2');
        $fixture->setTelefono('Value2');
        $fixture->setObservaciones('Value2');
        $fixture->setFormadorRJ(2);
        $fixture->setUsuario($usuario);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Eliminar');

        self::assertResponseRedirects($this->path);
        self::assertSame(0, $this->repository->count([]));
    }
    public function testAppend(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');

        // Creamos una entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24201');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(0);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Crearmos una entidad Edicion asociada al Curso
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24201/01');
        $edicion->setEstado(0);
        $edicion->setSesiones(2);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);
        $curso->addEdiciones($edicion);
        $this->manager->persist($edicion);

        // Creamos una entidad Formador
        $fixture = new Formador();
        $fixture->setNif('Value3');
        $fixture->setApellidos('Value3');
        $fixture->setNombre('Value3');
        $fixture->setOrganizacion('Value3');
        $fixture->setCorreoAux('Value3');
        $fixture->setTelefono('Value3');
        $fixture->setObservaciones('Value3');
        $fixture->setFormadorRJ(2);
        $fixture->setUsuario($usuario);
        $this->manager->persist($fixture);

        // Persistimos todos los datos en la base de datos
        $this->manager->flush();

        // Obtenemos el ID generado automáticamente para la Edicion
        $id = $edicion->getId();

        // Realizamos la solicitud al controlador
        $this->client->request('GET', "/intranet/forpas/gestor/formador/append/$id");

        // Verificamos el código de respuesta HTTP
        self::assertResponseStatusCodeSame(200);

        // Verificamos que la vista contiene los datos de los participantes disponibles
        self::assertSelectorTextContains(
            '#datosFormadoresAsignables tbody tr:first-child td:nth-child(1)',
            'Value3'
        );
    }
}
