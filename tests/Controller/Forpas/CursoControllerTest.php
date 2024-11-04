<?php

namespace App\Tests\Controller\Forpas;

use App\Entity\Forpas\Curso;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CursoControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /**
     * @var EntityRepository<Curso>
     */
    private EntityRepository $repository;
    private string $path = '/intranet/forpas/gestor/curso/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Curso::class);

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
        self::assertPageTitleContains('SIRHUS: Servicio de Formación');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));
        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Guardar', [
            'curso[codigo_curso]' => 'Test',
            'curso[nombre_curso]' => 'Testing',
            'curso[horas]' => 20,
            'curso[objetivos]' => 'Testing',
            'curso[contenidos]' => 'Testing',
            'curso[destinatarios]' => 'Testing',
            'curso[requisitos]' => 'Testing',
            'curso[justificacion]' => 'Testing',
            'curso[coordinador]' => 'Testing',
            'curso[participantes_edicion]' => 20,
            'curso[ediciones_estimadas]' => 2,
            'curso[plazo_solicitud]' => 'Testing',
            'curso[observaciones]' => 'Testing',
            'curso[visible_web]' => 1,
            'curso[horas_virtuales]' => 20,
            'curso[calificable]' => 1,
        ]);

        self::assertResponseRedirects($this->path);
        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $fixture = new Curso();
        $fixture->setCodigoCurso('My Title');
        $fixture->setNombreCurso('My Title');
        $fixture->setHoras(20);
        $fixture->setObjetivos('My Title');
        $fixture->setContenidos('My Title');
        $fixture->setDestinatarios('My Title');
        $fixture->setRequisitos('My Title');
        $fixture->setJustificacion('My Title');
        $fixture->setCoordinador('My Title');
        $fixture->setParticipantesEdicion(20);
        $fixture->setEdicionesEstimadas(2);
        $fixture->setPlazoSolicitud('My Title');
        $fixture->setObservaciones('My Title');
        $fixture->setVisibleWeb(1);
        $fixture->setHorasVirtuales(20);
        $fixture->setCalificable(1);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('SIRHUS: Servicio de Formación');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $fixture = new Curso();
        $fixture->setCodigoCurso('Value');
        $fixture->setNombreCurso('Value');
        $fixture->setHoras(20);
        $fixture->setObjetivos('Value');
        $fixture->setContenidos('Value');
        $fixture->setDestinatarios('Value');
        $fixture->setRequisitos('Value');
        $fixture->setJustificacion('Value');
        $fixture->setCoordinador('Value');
        $fixture->setParticipantesEdicion(20);
        $fixture->setEdicionesEstimadas(2);
        $fixture->setPlazoSolicitud('Value');
        $fixture->setObservaciones('Value');
        $fixture->setVisibleWeb(1);
        $fixture->setHorasVirtuales(20);
        $fixture->setCalificable(1);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Actualizar', [
            'curso[codigo_curso]' => 'New',
            'curso[nombre_curso]' => 'Something New',
            'curso[horas]' => 20,
            'curso[objetivos]' => 'Something New',
            'curso[contenidos]' => 'Something New',
            'curso[destinatarios]' => 'Something New',
            'curso[requisitos]' => 'Something New',
            'curso[justificacion]' => 'Something New',
            'curso[coordinador]' => 'Something New',
            'curso[participantes_edicion]' => 20,
            'curso[ediciones_estimadas]' => 2,
            'curso[plazo_solicitud]' => 'Something New',
            'curso[observaciones]' => 'Something New',
            'curso[visible_web]' => 1,
            'curso[horas_virtuales]' => 20,
            'curso[calificable]' => 1,
        ]);

        self::assertResponseRedirects('/intranet/forpas/gestor/curso/');

        $fixture = $this->repository->findAll();

        self::assertSame('New', $fixture[0]->getCodigoCurso());
        self::assertSame('Something New', $fixture[0]->getNombreCurso());
        self::assertSame(20, $fixture[0]->getHoras());
        self::assertSame('Something New', $fixture[0]->getObjetivos());
        self::assertSame('Something New', $fixture[0]->getContenidos());
        self::assertSame('Something New', $fixture[0]->getDestinatarios());
        self::assertSame('Something New', $fixture[0]->getRequisitos());
        self::assertSame('Something New', $fixture[0]->getJustificacion());
        self::assertSame('Something New', $fixture[0]->getCoordinador());
        self::assertSame(20, $fixture[0]->getParticipantesEdicion());
        self::assertSame(2, $fixture[0]->getEdicionesEstimadas());
        self::assertSame('Something New', $fixture[0]->getPlazoSolicitud());
        self::assertSame('Something New', $fixture[0]->getObservaciones());
        self::assertTrue( $fixture[0]->isVisibleWeb());
        self::assertSame(20, $fixture[0]->getHorasVirtuales());
        self::assertTrue( $fixture[0]->isCalificable());
    }
    public function testRemove(): void
    {
        $fixture = new Curso();
        $fixture->setCodigoCurso('Value');
        $fixture->setNombreCurso('Value');
        $fixture->setHoras(20);
        $fixture->setObjetivos('Value');
        $fixture->setContenidos('Value');
        $fixture->setDestinatarios('Value');
        $fixture->setRequisitos('Value');
        $fixture->setJustificacion('Value');
        $fixture->setCoordinador('Value');
        $fixture->setParticipantesEdicion(20);
        $fixture->setEdicionesEstimadas(2);
        $fixture->setPlazoSolicitud('Value');
        $fixture->setObservaciones('Value');
        $fixture->setVisibleWeb(1);
        $fixture->setHorasVirtuales(20);
        $fixture->setCalificable(1);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Eliminar');

        self::assertResponseRedirects('/intranet/forpas/gestor/curso/');
        self::assertSame(0, $this->repository->count([]));
    }
    public function testAddEdiciones(): void
    {
        $curso = new Curso();
        $edicion = new \App\Entity\Forpas\Edicion();

        $curso->addEdiciones($edicion);

        $this->assertTrue($curso->getEdiciones()->contains($edicion));
        $this->assertSame($curso, $edicion->getCurso());
    }

    public function testRemoveEdiciones(): void
    {
        $curso = new Curso();
        $edicion = new \App\Entity\Forpas\Edicion();

        $curso->addEdiciones($edicion);
        $curso->removeEdiciones($edicion);

        $this->assertFalse($curso->getEdiciones()->contains($edicion));
        $this->assertNull($edicion->getCurso());
    }
}
