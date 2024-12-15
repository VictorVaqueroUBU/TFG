<?php

namespace App\Tests\Controller\Forpas;

use App\Entity\Forpas\Curso;
use App\Entity\Forpas\Edicion;
use App\Entity\Forpas\Participante;
use App\Entity\Forpas\ParticipanteEdicion;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\SchemaTool;

final class EdicionControllerTest extends BaseControllerTest
{
    /**
     * @var EntityRepository<Edicion>
     */
    private EntityRepository $repository;
    private string $path = '/intranet/forpas/gestor/edicion/';
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
        $this->repository = $this->manager->getRepository(Edicion::class);
    }
    public function testIndex(): void
    {
        $this->client->followRedirects();
        // Caso 1: Sin cursoId (debería ejecutarse el bloque `else`)
        $this->client->request('GET', $this->path);
        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Listado de ediciones');

        // Caso 2: Con cursoId (debería ejecutarse el bloque `if ($cursoId)`)
        $cursoId = 1;
        $this->client->request('GET', $this->path, ['cursoId' => $cursoId]);
        self::assertResponseStatusCodeSame(200);
    }

    public function testRemitir(): void
    {
        // Creamos un curso asociado a las ediciones
        $curso = new Curso();
        $curso->setCodigoCurso('25001');
        $curso->setNombreCurso('Curso Test Remitir');
        $curso->setHoras(10);
        $curso->setParticipantesEdicion(10);
        $curso->setEdicionesEstimadas(1);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(0);
        $curso->setCalificable(false);
        $this->manager->persist($curso);

        // Creamos varias ediciones, algunas con estado=1 (remitidas) y otras con distinto estado
        $edicionRemitida1 = new Edicion();
        $edicionRemitida1->setCodigoEdicion('RMT-001');
        $edicionRemitida1->setFechaInicio(new DateTime('2024-01-01'));
        $edicionRemitida1->setFechaFin(new DateTime('2024-01-02'));
        $edicionRemitida1->setCalendario('Calendario 1');
        $edicionRemitida1->setHorario('Horario 1');
        $edicionRemitida1->setLugar('Lugar 1');
        $edicionRemitida1->setEstado(1);
        $edicionRemitida1->setSesiones(2);
        $edicionRemitida1->setMaxParticipantes(20);
        $edicionRemitida1->setCurso($curso);
        $this->manager->persist($edicionRemitida1);

        $edicionRemitida2 = new Edicion();
        $edicionRemitida2->setCodigoEdicion('RMT-002');
        $edicionRemitida2->setFechaInicio(new DateTime('2024-01-03'));
        $edicionRemitida2->setFechaFin(new DateTime('2024-01-04'));
        $edicionRemitida2->setCalendario('Calendario 2');
        $edicionRemitida2->setHorario('Horario 2');
        $edicionRemitida2->setLugar('Lugar 2');
        $edicionRemitida2->setEstado(1);
        $edicionRemitida2->setSesiones(3);
        $edicionRemitida2->setMaxParticipantes(30);
        $edicionRemitida2->setCurso($curso);
        $this->manager->persist($edicionRemitida2);

        // Edición con estado distinto de 1 (ej. estado=0) que no debería aparecer
        $edicionNoRemitida = new Edicion();
        $edicionNoRemitida->setCodigoEdicion('NRMT-001');
        $edicionNoRemitida->setFechaInicio(new DateTime('2024-01-05'));
        $edicionNoRemitida->setFechaFin(new DateTime('2024-01-06'));
        $edicionNoRemitida->setCalendario('Calendario 3');
        $edicionNoRemitida->setHorario('Horario 3');
        $edicionNoRemitida->setLugar('Lugar 3');
        $edicionNoRemitida->setEstado(0);
        $edicionNoRemitida->setSesiones(1);
        $edicionNoRemitida->setMaxParticipantes(10);
        $edicionNoRemitida->setCurso($curso);
        $this->manager->persist($edicionNoRemitida);

        // Guardamos en la base de datos
        $this->manager->flush();

        // Realizamos la petición
        $this->client->request('GET', '/intranet/forpas/gestor/edicion/remitidas');

        // Verificamos que la respuesta es exitosa
        self::assertResponseStatusCodeSame(200);

        // Opcional: Comprobamos que en el contenido se muestran las ediciones remitidas
        $responseContent = $this->client->getResponse()->getContent();
        self::assertStringContainsString('RMT-001', $responseContent);
        self::assertStringContainsString('RMT-002', $responseContent);

        // Y comprobamos que la edición no remitida no aparece en el HTML
        self::assertStringNotContainsString('NRMT-001', $responseContent);

        // Si quieres además comprobar el título definido en defaults['titulo'],
        // podrías verificar algo así (suponiendo que el título se imprime en alguna parte):
        // self::assertStringContainsString('Ediciones remitidas para certificar', $responseContent);
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
        $fixture->setFechaInicio(new DateTime('2024-01-01'));
        $fixture->setFechaFin(new DateTime('2024-01-01'));
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
    }
    public function testEdit(): void
    {
        $fixture = new Edicion();
        $fixture->setCodigoEdicion('24003/01');
        $fixture->setFechaInicio(new DateTime('2024-01-01'));
        $fixture->setFechaFin(new DateTime('2024-01-02'));
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
        self::assertEquals(new DateTime('2024-01-01'), $updatedFixture->getFechaInicio());
        self::assertEquals(new DateTime('2024-01-02'), $updatedFixture->getFechaFin());
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
        $fixture->setFechaInicio(new DateTime('2024-01-01'));
        $fixture->setFechaFin(new DateTime('2024-01-01'));
        $fixture->setCalendario('Value');
        $fixture->setHorario('Value');
        $fixture->setLugar('Value');
        $fixture->setEstado(0);
        $fixture->setSesiones(2);
        $fixture->setMaxParticipantes(20);

        $curso = new Curso();
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
    public function testRemoveWithParticipantesAsociados(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');

        // Creamos el curso
        $curso = new Curso();
        $curso->setCodigoCurso('24001');
        $curso->setNombreCurso('Curso con participantes en edición');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(20);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos la edición
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('ValueEdicion');
        $edicion->setFechaInicio(new DateTime('2024-01-01'));
        $edicion->setFechaFin(new DateTime('2024-01-01'));
        $edicion->setCalendario('Value');
        $edicion->setHorario('Value');
        $edicion->setLugar('Value');
        $edicion->setEstado(0);
        $edicion->setSesiones(2);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);
        $this->manager->persist($edicion);

        // Creamos el participante
        $participante = new Participante();
        $participante->setNif('11111111A');
        $participante->setNombre('Participante');
        $participante->setApellidos('Prueba');
        $participante->setOrganizacion('Organización de Prueba');
        $participante->setUsuario($usuario);
        $this->manager->persist($participante);

        $this->manager->flush();

        // Creamos la entidad intermedia ParticipanteEdicion
        $participanteEdicion = new ParticipanteEdicion();
        $participanteEdicion->setParticipante($participante);
        $participanteEdicion->setEdicion($edicion);
        $participanteEdicion->setFechaSolicitud(new DateTime('2024-01-01'));
        $this->manager->persist($participanteEdicion);

        $this->manager->flush();

        // Ahora intentamos eliminar la edición con participantes asociados
        $this->client->request('GET', sprintf('%s%s', $this->path, $edicion->getId()));
        $this->client->submitForm('Eliminar');

        // Debe redirigir y mostrar el mensaje de advertencia
        self::assertResponseRedirects('/intranet/forpas/gestor/edicion/?cursoId=' . $curso->getId());

        $this->client->followRedirect();
        $responseContent = $this->client->getResponse()->getContent();
        self::assertStringContainsString('No se puede eliminar la edición porque tiene participantes inscritos.', $responseContent);

        // Comprobamos que la edición no se haya eliminado
        self::assertSame(1, $this->repository->count([]));
    }

}
