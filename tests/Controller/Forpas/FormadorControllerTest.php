<?php

namespace App\Tests\Controller\Forpas;

use App\Entity\Forpas\Curso;
use App\Entity\Forpas\Edicion;
use App\Entity\Forpas\Formador;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FormadorControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /**
     * @var EntityRepository<Formador>
     */
    private EntityRepository $repository;
    private string $path = '/intranet/forpas/gestor/formador/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();

        // Limpiar datos de ParticipanteEdicion, Edicion, Curso y Participante
        $repositories = [
            Formador::class,
            Edicion::class,
            Curso::class,
        ];

        foreach ($repositories as $repositoryClass) {
            $repository = $this->manager->getRepository($repositoryClass);
            foreach ($repository->findAll() as $object) {
                $this->manager->remove($object);
            }
        }

        $this->manager->flush();

        // Asigna el repositorio de Participante para los tests
        $this->repository = $this->manager->getRepository(Formador::class);
    }


    public function testIndex(): void
    {
        $this->client->followRedirects();
        $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Listado de Formadores');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }
    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Guardar', [
            'formador[nif]' => 'Testing',
            'formador[apellidos]' => 'Testing',
            'formador[nombre]' => 'Testing',
            'formador[organizacion]' => 'Testing',
            'formador[correo]' => 'Testing',
            'formador[telefono]' => 'Testing',
            'formador[observaciones]' => 'Testing',
            'formador[formadorRJ]' => 1,
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $fixture = new Formador();
        $fixture->setNif('My Title');
        $fixture->setApellidos('My Title');
        $fixture->setNombre('My Title');
        $fixture->setOrganizacion('My Title');
        $fixture->setCorreo('My Title');
        $fixture->setTelefono('My Title');
        $fixture->setObservaciones('My Title');
        $fixture->setFormadorRJ(1);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Datos del Formador');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $fixture = new Formador();
        $fixture->setNif('Value');
        $fixture->setApellidos('Value');
        $fixture->setNombre('Value');
        $fixture->setOrganizacion('Value');
        $fixture->setCorreo('Value');
        $fixture->setTelefono('Value');
        $fixture->setObservaciones('Value');
        $fixture->setFormadorRJ(1);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Actualizar', [
            'formador[nif]' => 'Something',
            'formador[apellidos]' => 'Something New',
            'formador[nombre]' => 'Something New',
            'formador[organizacion]' => 'Something New',
            'formador[correo]' => 'Something New',
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
        self::assertSame('Something New', $fixture[0]->getCorreo());
        self::assertSame('Something New', $fixture[0]->getTelefono());
        self::assertSame('Something New', $fixture[0]->getObservaciones());
        self::assertSame(1, $fixture[0]->getFormadorRJ());
    }

    public function testRemove(): void
    {
        $fixture = new Formador();
        $fixture->setNif('Value2');
        $fixture->setApellidos('Value2');
        $fixture->setNombre('Value2');
        $fixture->setOrganizacion('Value2');
        $fixture->setCorreo('Value2');
        $fixture->setTelefono('Value2');
        $fixture->setObservaciones('Value2');
        $fixture->setFormadorRJ(2);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Eliminar');

        self::assertResponseRedirects($this->path);
        self::assertSame(0, $this->repository->count([]));
    }
    public function testAppend(): void
    {
        // Crear una entidad Curso
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

        // Crear una entidad Edicion asociada al Curso
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24201/01');
        $edicion->setEstado(0);
        $edicion->setSesiones(2);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);
        $curso->addEdiciones($edicion);
        $this->manager->persist($edicion);

        // Crear una entidad Formador
        $fixture = new Formador();
        $fixture->setNif('Value3');
        $fixture->setApellidos('Value3');
        $fixture->setNombre('Value3');
        $fixture->setOrganizacion('Value3');
        $fixture->setCorreo('Value3');
        $fixture->setTelefono('Value3');
        $fixture->setObservaciones('Value3');
        $fixture->setFormadorRJ(2);
        $this->manager->persist($fixture);

        // Persistir todos los datos en la base de datos
        $this->manager->flush();

        // Obtener el ID generado automáticamente para la Edicion
        $id = $edicion->getId();

        // Realizar la solicitud al controlador
        $this->client->request('GET', "/intranet/forpas/gestor/formador/append/$id");

        // Verificar el código de respuesta HTTP
        self::assertResponseStatusCodeSame(200);

        // Verificar que la vista contiene los datos de los participantes disponibles
        self::assertSelectorTextContains(
            '#datosFormadoresAsignables tbody tr:first-child td:nth-child(1)',
            'Value3' // Cambia esto por el NIF esperado.
        );
    }
}
