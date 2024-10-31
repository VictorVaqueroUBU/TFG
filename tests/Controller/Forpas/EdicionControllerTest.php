<?php

namespace App\Tests\Controller\Forpas;

use App\Entity\Forpas\Edicion;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EdicionControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/edicion/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Edicion::class);

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
        self::assertPageTitleContains('Edicion index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'edicion[codigo_edicion]' => 'Testing',
            'edicion[fecha_inicio]' => 'Testing',
            'edicion[fecha_fin]' => 'Testing',
            'edicion[calendario]' => 'Testing',
            'edicion[horario]' => 'Testing',
            'edicion[lugar]' => 'Testing',
            'edicion[estado]' => 'Testing',
            'edicion[sesiones]' => 'Testing',
            'edicion[max_participantes]' => 'Testing',
            'edicion[curso]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Edicion();
        $fixture->setCodigo_edicion('My Title');
        $fixture->setFecha_inicio('My Title');
        $fixture->setFecha_fin('My Title');
        $fixture->setCalendario('My Title');
        $fixture->setHorario('My Title');
        $fixture->setLugar('My Title');
        $fixture->setEstado('My Title');
        $fixture->setSesiones('My Title');
        $fixture->setMax_participantes('My Title');
        $fixture->setCurso('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Edicion');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Edicion();
        $fixture->setCodigo_edicion('Value');
        $fixture->setFecha_inicio('Value');
        $fixture->setFecha_fin('Value');
        $fixture->setCalendario('Value');
        $fixture->setHorario('Value');
        $fixture->setLugar('Value');
        $fixture->setEstado('Value');
        $fixture->setSesiones('Value');
        $fixture->setMax_participantes('Value');
        $fixture->setCurso('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'edicion[codigo_edicion]' => 'Something New',
            'edicion[fecha_inicio]' => 'Something New',
            'edicion[fecha_fin]' => 'Something New',
            'edicion[calendario]' => 'Something New',
            'edicion[horario]' => 'Something New',
            'edicion[lugar]' => 'Something New',
            'edicion[estado]' => 'Something New',
            'edicion[sesiones]' => 'Something New',
            'edicion[max_participantes]' => 'Something New',
            'edicion[curso]' => 'Something New',
        ]);

        self::assertResponseRedirects('/edicion/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getCodigo_edicion());
        self::assertSame('Something New', $fixture[0]->getFecha_inicio());
        self::assertSame('Something New', $fixture[0]->getFecha_fin());
        self::assertSame('Something New', $fixture[0]->getCalendario());
        self::assertSame('Something New', $fixture[0]->getHorario());
        self::assertSame('Something New', $fixture[0]->getLugar());
        self::assertSame('Something New', $fixture[0]->getEstado());
        self::assertSame('Something New', $fixture[0]->getSesiones());
        self::assertSame('Something New', $fixture[0]->getMax_participantes());
        self::assertSame('Something New', $fixture[0]->getCurso());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Edicion();
        $fixture->setCodigo_edicion('Value');
        $fixture->setFecha_inicio('Value');
        $fixture->setFecha_fin('Value');
        $fixture->setCalendario('Value');
        $fixture->setHorario('Value');
        $fixture->setLugar('Value');
        $fixture->setEstado('Value');
        $fixture->setSesiones('Value');
        $fixture->setMax_participantes('Value');
        $fixture->setCurso('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/edicion/');
        self::assertSame(0, $this->repository->count([]));
    }
}
