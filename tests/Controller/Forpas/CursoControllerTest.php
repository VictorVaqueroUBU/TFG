<?php

namespace App\Tests\Controller\Forpas;

use App\Entity\Forpas\Curso;
use App\Entity\Forpas\Edicion;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\SchemaTool;

final class CursoControllerTest extends BaseControllerTest
{
    /**
     * @var EntityRepository<Curso>
     */
    private EntityRepository $repository;
    private string $path = '/intranet/forpas/gestor/curso/';
    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = static::getContainer()->get('doctrine')->getManager();
        // Limpieza completa de la base de datos
        $schemaTool = new SchemaTool($this->manager);
        $classes = $this->manager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);

        $this->manager->flush();

        // Creamos y autenticamos un usuario por defecto
        $this->client->loginUser($this->createUserWithRole('ROLE_ADMIN'));
        // Asignamos el repositorio de Curso para los tests
        $this->repository = $this->manager->getRepository(Curso::class);
    }
    public function testIndex(): void
    {
        $this->client->followRedirects();
        $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Listado de Cursos');
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
        $fixture->setVisibleWeb(true);
        $fixture->setHorasVirtuales(20);
        $fixture->setCalificable(true);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Datos del Curso');
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
        $fixture->setVisibleWeb(true);
        $fixture->setHorasVirtuales(20);
        $fixture->setCalificable(true);

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
        self::assertSame(20.0, $fixture[0]->getHoras());
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
        self::assertSame(20.0, $fixture[0]->getHorasVirtuales());
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
        $fixture->setVisibleWeb(true);
        $fixture->setHorasVirtuales(20);
        $fixture->setCalificable(true);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Eliminar');

        self::assertResponseRedirects('/intranet/forpas/gestor/curso/');
        self::assertSame(0, $this->repository->count([]));
    }
    public function testRemoveWithEdicionesAsociadas(): void
    {
        $curso = new Curso();
        $curso->setCodigoCurso('Value');
        $curso->setNombreCurso('Value');
        $curso->setHoras(20);
        $curso->setObjetivos('Value');
        $curso->setContenidos('Value');
        $curso->setDestinatarios('Value');
        $curso->setRequisitos('Value');
        $curso->setJustificacion('Value');
        $curso->setCoordinador('Value');
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setPlazoSolicitud('Value');
        $curso->setObservaciones('Value');
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(20);
        $curso->setCalificable(true);

        $this->manager->persist($curso);

        // Creamos una edición asociada al curso
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('ValueEdicion');
        $edicion->setFechaInicio(new DateTime('2024-01-01'));
        $edicion->setFechaFin(new DateTime('2024-01-02'));
        $edicion->setCalendario('Calendario test');
        $edicion->setHorario('Horario test');
        $edicion->setLugar('Lugar test');
        $edicion->setEstado(0);
        $edicion->setSesiones(2);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);

        $this->manager->persist($edicion);
        $this->manager->flush();

        // Ahora intentamos eliminar el curso con ediciones asociadas
        $this->client->request('GET', sprintf('%s%s', $this->path, $curso->getId()));
        $this->client->submitForm('Eliminar');

        // Comprobamos el redirect
        self::assertResponseRedirects('/intranet/forpas/gestor/curso/');

        // Seguimos el redirect y comprobamos el mensaje flash
        $this->client->followRedirect();
        $responseContent = $this->client->getResponse()->getContent();
        self::assertStringContainsString('No se puede eliminar el curso porque tiene ediciones creadas.', $responseContent);

        // El curso no debe haberse eliminado
        self::assertSame(1, $this->repository->count([]));
    }

    public function testAddEdiciones(): void
    {
        $curso = new Curso();
        $edicion = new Edicion();

        $curso->addEdiciones($edicion);

        $this->assertTrue($curso->getEdiciones()->contains($edicion));
        $this->assertSame($curso, $edicion->getCurso());
    }
    public function testRemoveEdiciones(): void
    {
        $curso = new Curso();
        $edicion = new Edicion();

        $curso->addEdiciones($edicion);
        $curso->removeEdiciones($edicion);

        $this->assertFalse($curso->getEdiciones()->contains($edicion));
        $this->assertNull($edicion->getCurso());
    }
}
