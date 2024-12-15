<?php

namespace App\Tests\Controller\Forpas;

use App\Entity\Forpas\Curso;
use App\Entity\Forpas\Edicion;
use App\Entity\Forpas\Formador;
use App\Entity\Forpas\FormadorEdicion;
use App\Entity\Forpas\Participante;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\SchemaTool;

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

        $this->manager = static::getContainer()->get('doctrine')->getManager();
        // Limpieza completa de la base de datos
        $schemaTool = new SchemaTool($this->manager);
        $classes = $this->manager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);

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
    public function testFind(): void
    {
        // Creamos un formador para tener datos visibles
        $usuario = $this->createUserWithRole('ROLE_USER');

        $formador = new Formador();
        $formador->setNif('FIND-TEST');
        $formador->setNombre('Nombre de prueba');
        $formador->setApellidos('Apellidos de prueba');
        $formador->setOrganizacion('Organización de prueba');
        $formador->setCorreoAux('aux@test.com');
        $formador->setTelefono('123456789');
        $formador->setObservaciones('Observaciones de prueba');
        $formador->setFormadorRJ(0);
        $formador->setUsuario($usuario);

        $this->manager->persist($formador);
        $this->manager->flush();

        // Realizamos la petición al método find
        $this->client->request('GET', $this->path . 'find');

        // Verificamos el estado de la respuesta
        self::assertResponseStatusCodeSame(200);

        // Verificamos que el título de la página corresponde al esperado (en defaults['titulo'])
        self::assertPageTitleContains('Listado de Formadores');

        // Opcional: Comprobamos que el contenido del formador creado aparece en la respuesta
        $responseContent = $this->client->getResponse()->getContent();
        self::assertStringContainsString('FIND-TEST', $responseContent);
        self::assertStringContainsString('Nombre de prueba', $responseContent);
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
    public function testDelete(): void
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
    public function testRemove(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');

        // Creamos un participante asociado al mismo usuario, para que luego al eliminar el Formador
        // se cumpla la condición if ($participante).
        $participante = new Participante();
        $participante->setNif('12345678B');
        $participante->setNombre('Participante');
        $participante->setApellidos('De prueba');
        $participante->setUsuario($usuario);
        $participante->setOrganizacion('Organización Ejemplo');
        $this->manager->persist($participante);

        $formador = new Formador();
        $formador->setNif('Value2');
        $formador->setApellidos('Value2');
        $formador->setNombre('Value2');
        $formador->setOrganizacion('Value2');
        $formador->setCorreoAux('Value2');
        $formador->setTelefono('Value2');
        $formador->setObservaciones('Value2');
        $formador->setFormadorRJ(2);
        $formador->setUsuario($usuario);

        $this->manager->persist($formador);
        $this->manager->flush();

        // Ahora eliminamos el formador
        $this->client->request('GET', sprintf('%s%s', $this->path, $formador->getId()));
        $this->client->submitForm('Eliminar');

        // Verificamos que se hizo el redirect
        self::assertResponseRedirects($this->path);

        // Verificamos que el formador fue eliminado
        self::assertSame(0, $this->repository->count([]));

        // Ahora, dado que existía un participante para el mismo usuario,
        // el código del if ($participante) se habrá ejecutado,
        // lo que debería mejorar la cobertura.
    }
    public function testRemoveWithEdicionesAsociadas(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');

        // Crear el Formador
        $formador = new Formador();
        $formador->setNif('Value4');
        $formador->setApellidos('Value4');
        $formador->setNombre('Value4');
        $formador->setOrganizacion('Value4');
        $formador->setCorreoAux('Value4');
        $formador->setTelefono('Value4');
        $formador->setObservaciones('Value4');
        $formador->setFormadorRJ(2);
        $formador->setUsuario($usuario);
        $this->manager->persist($formador);

        // Crear el Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba Eliminación');
        $curso->setCodigoCurso('99999');
        $curso->setHoras(10);
        $curso->setParticipantesEdicion(10);
        $curso->setEdicionesEstimadas(1);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(0);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Crear la Edición asociada al Curso
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('99999/01');
        $edicion->setEstado(0);
        $edicion->setSesiones(2);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);
        $this->manager->persist($edicion);

        // Persistimos para generar IDs
        $this->manager->flush();

        // Crear la entidad intermedia FormadorEdicion
        $formadorEdicion = new FormadorEdicion();
        $formadorEdicion->setFormador($formador);
        $formadorEdicion->setEdicion($edicion);
        // Opcionalmente setear otros campos si es necesario
        $this->manager->persist($formadorEdicion);

        $this->manager->flush();

        // Ahora intentamos eliminar el Formador
        $this->client->request('GET', sprintf('%s%s', $this->path, $formador->getId()));
        $this->client->submitForm('Eliminar');

        // Al haber una edición asociada, deberíamos obtener el mensaje de advertencia.
        self::assertResponseRedirects($this->path);

        $this->client->followRedirect();
        $responseContent = $this->client->getResponse()->getContent();
        self::assertStringContainsString('No se puede eliminar al formador porque tiene ediciones asociadas.', $responseContent);
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
