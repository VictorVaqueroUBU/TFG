<?php

namespace App\Tests\Controller\Gestor;

use App\Entity\Curso;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CursoControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/curso/';

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
        self::assertPageTitleContains('Curso index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'curso[codigo_curso]' => 'Testing',
            'curso[nombre_curso]' => 'Testing',
            'curso[horas]' => 'Testing',
            'curso[objetivos]' => 'Testing',
            'curso[contenidos]' => 'Testing',
            'curso[destinatarios]' => 'Testing',
            'curso[requisitos]' => 'Testing',
            'curso[justificacion]' => 'Testing',
            'curso[coordinador]' => 'Testing',
            'curso[participantes_edicion]' => 'Testing',
            'curso[ediciones_estimadas]' => 'Testing',
            'curso[plazo_solicitud]' => 'Testing',
            'curso[observaciones]' => 'Testing',
            'curso[visible_web]' => 'Testing',
            'curso[id_programa]' => 'Testing',
            'curso[horas_virtuales]' => 'Testing',
            'curso[calificable]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Curso();
        $fixture->setCodigo_curso('My Title');
        $fixture->setNombre_curso('My Title');
        $fixture->setHoras('My Title');
        $fixture->setObjetivos('My Title');
        $fixture->setContenidos('My Title');
        $fixture->setDestinatarios('My Title');
        $fixture->setRequisitos('My Title');
        $fixture->setJustificacion('My Title');
        $fixture->setCoordinador('My Title');
        $fixture->setParticipantes_edicion('My Title');
        $fixture->setEdiciones_estimadas('My Title');
        $fixture->setPlazo_solicitud('My Title');
        $fixture->setObservaciones('My Title');
        $fixture->setVisible_web('My Title');
        $fixture->setId_programa('My Title');
        $fixture->setHoras_virtuales('My Title');
        $fixture->setCalificable('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Curso');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Curso();
        $fixture->setCodigo_curso('Value');
        $fixture->setNombre_curso('Value');
        $fixture->setHoras('Value');
        $fixture->setObjetivos('Value');
        $fixture->setContenidos('Value');
        $fixture->setDestinatarios('Value');
        $fixture->setRequisitos('Value');
        $fixture->setJustificacion('Value');
        $fixture->setCoordinador('Value');
        $fixture->setParticipantes_edicion('Value');
        $fixture->setEdiciones_estimadas('Value');
        $fixture->setPlazo_solicitud('Value');
        $fixture->setObservaciones('Value');
        $fixture->setVisible_web('Value');
        $fixture->setId_programa('Value');
        $fixture->setHoras_virtuales('Value');
        $fixture->setCalificable('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'curso[codigo_curso]' => 'Something New',
            'curso[nombre_curso]' => 'Something New',
            'curso[horas]' => 'Something New',
            'curso[objetivos]' => 'Something New',
            'curso[contenidos]' => 'Something New',
            'curso[destinatarios]' => 'Something New',
            'curso[requisitos]' => 'Something New',
            'curso[justificacion]' => 'Something New',
            'curso[coordinador]' => 'Something New',
            'curso[participantes_edicion]' => 'Something New',
            'curso[ediciones_estimadas]' => 'Something New',
            'curso[plazo_solicitud]' => 'Something New',
            'curso[observaciones]' => 'Something New',
            'curso[visible_web]' => 'Something New',
            'curso[id_programa]' => 'Something New',
            'curso[horas_virtuales]' => 'Something New',
            'curso[calificable]' => 'Something New',
        ]);

        self::assertResponseRedirects('/curso/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getCodigo_curso());
        self::assertSame('Something New', $fixture[0]->getNombre_curso());
        self::assertSame('Something New', $fixture[0]->getHoras());
        self::assertSame('Something New', $fixture[0]->getObjetivos());
        self::assertSame('Something New', $fixture[0]->getContenidos());
        self::assertSame('Something New', $fixture[0]->getDestinatarios());
        self::assertSame('Something New', $fixture[0]->getRequisitos());
        self::assertSame('Something New', $fixture[0]->getJustificacion());
        self::assertSame('Something New', $fixture[0]->getCoordinador());
        self::assertSame('Something New', $fixture[0]->getParticipantes_edicion());
        self::assertSame('Something New', $fixture[0]->getEdiciones_estimadas());
        self::assertSame('Something New', $fixture[0]->getPlazo_solicitud());
        self::assertSame('Something New', $fixture[0]->getObservaciones());
        self::assertSame('Something New', $fixture[0]->getVisible_web());
        self::assertSame('Something New', $fixture[0]->getId_programa());
        self::assertSame('Something New', $fixture[0]->getHoras_virtuales());
        self::assertSame('Something New', $fixture[0]->getCalificable());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Curso();
        $fixture->setCodigo_curso('Value');
        $fixture->setNombre_curso('Value');
        $fixture->setHoras('Value');
        $fixture->setObjetivos('Value');
        $fixture->setContenidos('Value');
        $fixture->setDestinatarios('Value');
        $fixture->setRequisitos('Value');
        $fixture->setJustificacion('Value');
        $fixture->setCoordinador('Value');
        $fixture->setParticipantes_edicion('Value');
        $fixture->setEdiciones_estimadas('Value');
        $fixture->setPlazo_solicitud('Value');
        $fixture->setObservaciones('Value');
        $fixture->setVisible_web('Value');
        $fixture->setId_programa('Value');
        $fixture->setHoras_virtuales('Value');
        $fixture->setCalificable('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/curso/');
        self::assertSame(0, $this->repository->count([]));
    }
}
