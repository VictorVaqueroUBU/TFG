<?php

namespace App\Tests\Controller\Forpas;

use App\Entity\Forpas\Curso;
use App\Entity\Forpas\Edicion;
use App\Entity\Forpas\Participante;
use App\Entity\Forpas\ParticipanteEdicion;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ParticipanteControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /**
     * @var EntityRepository<Participante>
     */
    private EntityRepository $repository;
    private string $path = '/intranet/forpas/gestor/participante/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Participante::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }
    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Listado de Participantes');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Guardar', [
            'participante[nif]' => 'Testing',
            'participante[apellidos]' => 'Testing',
            'participante[nombre]' => 'Testing',
            'participante[descripcion_cce]' => 'Testing',
            'participante[codigo_cce]' => 'Test',
            'participante[grupo]' => 'A1',
            'participante[nivel]' => 27,
            'participante[puesto_trabajo]' => 'Testing',
            'participante[subunidad]' => 'Testing',
            'participante[unidad]' => 'Testing',
            'participante[centro_destino]' => 'Testing',
            'participante[t_r_juridico]' => 'FC',
            'participante[situacion_admin]' => 'Testing',
            'participante[codigo_plaza]' => 'Testing',
            'participante[telefono_trabajo]' => 'Testing',
            'participante[correo_aux]' => 'Testing',
            'participante[codigo_rpt]' => 'Testing',
            'participante[organizacion]' => 'Testing',
            'participante[turno]' => 'Testing',
            'participante[telefono_particular]' => 'Testing',
            'participante[telefono_movil]' => 'Testing',
            'participante[fecha_nacimiento]' => '2024-01-01',
            'participante[titulacion_nivel]' => 1,
            'participante[titulacion_fecha]' => '2024-01-01',
            'participante[titulacion]' => 'Testing',
            'participante[dni_sin_letra]' => 'Testing',
            'participante[uvus]' => 'Testing',
            'participante[sexo]' => 'V',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }
    public function testShow(): void
    {
        $fixture = new Participante();
        $fixture->setNif('My Title');
        $fixture->setApellidos('My Title');
        $fixture->setNombre('My Title');
        $fixture->setDescripcionCce('My Title');
        $fixture->setCodigoCce('My Title');
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
        $fixture->setFechaNacimiento(new \DateTime('2024-01-01'));
        $fixture->setTitulacionNivel(1);
        $fixture->setTitulacionFecha(new \DateTime('2024-01-01'));
        $fixture->setTitulacion('My Title');
        $fixture->setDniSinLetra('My Title');
        $fixture->setUvus('My Title');
        $fixture->setSexo('V');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Datos del Participante');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
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
        $fixture->setFechaNacimiento(new \DateTime('2024-01-01'));
        $fixture->setTitulacionNivel(1);
        $fixture->setTitulacionFecha(new \DateTime('2024-01-01'));
        $fixture->setTitulacion('Value');
        $fixture->setDniSinLetra('Value');
        $fixture->setUvus('Value');
        $fixture->setSexo('V');

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
            'participante[uvus]' => 'Something New',
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
        self::assertEquals(new \DateTime('2024-01-01'), $fixture[0]->getFechaNacimiento());
        self::assertSame(1, $fixture[0]->getTitulacionNivel());
        self::assertEquals(new \DateTime('2024-01-01'), $fixture[0]->getTitulacionFecha());
        self::assertSame('Something New', $fixture[0]->getTitulacion());
        self::assertSame('Some', $fixture[0]->getDniSinLetra());
        self::assertSame('Something New', $fixture[0]->getUvus());
        self::assertSame('V', $fixture[0]->getSexo());
    }

    public function testRemove(): void
    {
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
        $fixture->setFechaNacimiento(new \DateTime('2024-01-01'));
        $fixture->setTitulacionNivel(1);
        $fixture->setTitulacionFecha(new \DateTime('2024-01-01'));
        $fixture->setTitulacion('Value');
        $fixture->setDniSinLetra('Value');
        $fixture->setUvus('Value');
        $fixture->setSexo('V');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Eliminar');

        self::assertResponseRedirects('/intranet/forpas/gestor/participante/', 303);
        self::assertSame(0, $this->repository->count([]));
    }
    public function testAppend(): void
    {
        // Crear una entidad Curso
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

        // Crear una entidad Edicion asociada al Curso
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24101/01');
        $edicion->setEstado(0);
        $edicion->setSesiones(2);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);
        $curso->addEdiciones($edicion);
        $this->manager->persist($edicion);

        // Crear una entidad Participante
        $participante = new Participante();
        $participante->setNif('12345678A');
        $participante->setNombre('John');
        $participante->setApellidos('Doe');
        $participante->setUnidad('Unidad');
        $this->manager->persist($participante);

        // Persistir todos los datos en la base de datos
        $this->manager->flush();

        // Obtener el ID generado automáticamente para la Edicion
        $id = $edicion->getId();

        // Realizar la solicitud al controlador
        $this->client->request('GET', "/intranet/forpas/gestor/participante/append/{$id}");

        // Verificar el código de respuesta HTTP
        self::assertResponseStatusCodeSame(200);

        // Verificar que la vista contiene los datos de los participantes disponibles
        self::assertSelectorTextContains(
            '#datosParticipantesSeleccionables tbody tr:first-child td:nth-child(1)',
            '12345678A' // Cambia esto por el NIF esperado.
        );
    }
}
