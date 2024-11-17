<?php

namespace App\Tests\Controller\Forpas;

use App\Entity\Forpas\Curso;
use App\Entity\Forpas\Edicion;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EdicionControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /**
     * @var EntityRepository<Edicion>
     */
    private EntityRepository $repository;
    private string $path = '/intranet/forpas/gestor/edicion/';
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();

        // Limpiar datos de ediciones y cursos
        $edicionRepository = $this->manager->getRepository(Edicion::class);
        foreach ($edicionRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $cursoRepository = $this->manager->getRepository(Curso::class);
        foreach ($cursoRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();

        $this->repository = $this->manager->getRepository(Edicion::class);
    }


    public function testIndex(): void
    {
        $this->client->followRedirects();
        // Caso 1: Sin cursoId (debería ejecutarse el bloque `else`)
        $crawler = $this->client->request('GET', $this->path);
        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Listado de ediciones');

        // Caso 2: Con cursoId (debería ejecutarse el bloque `if ($cursoId)`)
        $cursoId = 1; // Cambia este valor según un curso existente en tu base de datos de prueba
        $crawler = $this->client->request('GET', $this->path, ['cursoId' => $cursoId]);
        self::assertResponseStatusCodeSame(200);
    }
    public function testNew(): void
    {
        $curso = new Curso();
        $curso->setCodigoCurso('24001');
        $curso->setNombreCurso('Nombre del curso');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(20);
        $curso->setCalificable(true);

        $this->manager->persist($curso);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%snew/%d', $this->path, $curso->getId()));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Guardar', [
            'edicion[codigo_edicion]' => 'Testing',
            'edicion[fecha_inicio]' => '2024-01-01',
            'edicion[fecha_fin]' => '2024-01-01',
            'edicion[calendario]' => 'Testing',
            'edicion[horario]' => 'Testing',
            'edicion[lugar]' => 'Testing',
            'edicion[estado]' => 0,
            'edicion[sesiones]' => 2,
            'edicion[max_participantes]' => 20,
        ]);

        self::assertResponseRedirects($this->path . '?cursoId=' . $curso->getId());
        self::assertSame(1, $this->repository->count([]));
    }
    public function testShow(): void
    {
        $fixture = new Edicion();
        $fixture->setCodigoEdicion('24002/01');
        $fixture->setFechaInicio(new \DateTime('2024-01-01'));
        $fixture->setFechaFin(new \DateTime('2024-01-01'));
        $fixture->setCalendario('My Title');
        $fixture->setHorario('My Title');
        $fixture->setLugar('My Title');
        $fixture->setEstado(0);
        $fixture->setSesiones(2);
        $fixture->setMaxParticipantes(20);

        $curso = new Curso();
        $curso->setCodigoCurso('24002');
        $curso->setNombreCurso('Curso de prueba');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(20);
        $curso->setCalificable(true);

        $this->manager->persist($curso);
        $fixture->setCurso($curso);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Datos de la Edición');

        // Use assertions to check that the properties are properly displayed.
    }
    public function testEdit(): void
    {
        $fixture = new Edicion();
        $fixture->setCodigoEdicion('24003/01');
        $fixture->setFechaInicio(new \DateTime('2024-01-01'));
        $fixture->setFechaFin(new \DateTime('2024-01-02'));
        $fixture->setCalendario('Value');
        $fixture->setHorario('Value');
        $fixture->setLugar('Value');
        $fixture->setEstado(0);
        $fixture->setSesiones(2);
        $fixture->setMaxParticipantes(20);

        $curso = new Curso();
        $curso->setCodigoCurso('24003');
        $curso->setNombreCurso('Nombre del curso');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(20);
        $curso->setCalificable(true);

        $this->manager->persist($curso);
        $fixture->setCurso($curso);
        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('/intranet/forpas/gestor/edicion/%d/edit', $fixture->getId()));
        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Actualizar', [
            'edicion[codigo_edicion]' => 'Something New',
            'edicion[fecha_inicio]' => '2024-01-01',
            'edicion[fecha_fin]' => '2024-01-02',
            'edicion[calendario]' => 'Something New',
            'edicion[horario]' => 'Something New',
            'edicion[lugar]' => 'Something New',
            'edicion[estado]' => 0,
            'edicion[sesiones]' => 2,
            'edicion[max_participantes]' => 20,
            'edicion[curso]' => $curso->getId(),
        ]);

        self::assertResponseRedirects($this->path . '?cursoId=' . $curso->getId());

        $updatedFixture = $this->repository->find($fixture->getId());

        self::assertEquals(new \DateTime('2024-01-01'), $updatedFixture->getFechaInicio());
        self::assertEquals(new \DateTime('2024-01-02'), $updatedFixture->getFechaFin());
        self::assertSame('Something New', $updatedFixture->getCalendario());
        self::assertSame('Something New', $updatedFixture->getHorario());
        self::assertSame('Something New', $updatedFixture->getLugar());
        self::assertSame(0, $updatedFixture->getEstado());
        self::assertSame(2, $updatedFixture->getSesiones());
        self::assertSame(20, $updatedFixture->getMaxParticipantes());
        self::assertSame($curso->getId(), $updatedFixture->getCurso()->getId());
    }
    public function testRemove(): void
    {
        $fixture = new Edicion();
        $fixture->setCodigoEdicion('Value');
        $fixture->setFechaInicio(new \DateTime('2024-01-01'));
        $fixture->setFechaFin(new \DateTime('2024-01-01'));
        $fixture->setCalendario('Value');
        $fixture->setHorario('Value');
        $fixture->setLugar('Value');
        $fixture->setEstado(0);
        $fixture->setSesiones(2);
        $fixture->setMaxParticipantes(20);

        $curso = new \App\Entity\Forpas\Curso();
        $curso->setCodigoCurso('24001');
        $curso->setNombreCurso('Curso de prueba');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(20);
        $curso->setCalificable(true);

        $this->manager->persist($curso);
        $fixture->setCurso($curso);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Eliminar');

        self::assertResponseRedirects('/intranet/forpas/gestor/edicion/');
        self::assertSame(0, $this->repository->count([]));
    }
}
