<?php
namespace App\Tests\Controller\Forpas;

use App\Entity\Forpas\Curso;
use App\Entity\Forpas\Edicion;
use App\Entity\Forpas\Participante;
use App\Entity\Forpas\ParticipanteEdicion;
use App\Entity\Sistema\Usuario;
use App\Repository\Forpas\ParticipanteEdicionRepository;
use DateTime;
use Doctrine\ORM\Exception\ORMException;

class ParticipantePortalControllerTest extends BaseControllerTest
{
    /** @var ParticipanteEdicionRepository */
    protected ParticipanteEdicionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = static::getContainer()->get('doctrine')->getManager();

        // Limpiamos datos de ParticipanteEdicion, Edicion, Curso y Participante
        $repositories = [
            ParticipanteEdicion::class,
            Participante::class,
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

        // Configuramos el repositorio
        $this->repository = $this->manager->getRepository(ParticipanteEdicion::class);
    }
    public function testMisDatosFormularioCargaCorrectamente(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');

        $participante = new Participante();
        $participante->setNif('12345678A');
        $participante->setNombre('John');
        $participante->setApellidos('Doe');
        $participante->setUnidad('Unidad');
        $participante->setUsuario($usuario);

        $this->manager->persist($participante);
        $this->manager->flush();
        $this->manager->refresh($usuario);

        $this->client->loginUser($usuario);

        $this->client->request('GET', '/intranet/forpas/participante/mis-datos');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="participante_contacto"]');
    }
    public function testMisDatosActualizacionValida(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');

        $participante = new Participante();
        $participante->setNif('12345678A');
        $participante->setNombre('John');
        $participante->setApellidos('Doe');
        $participante->setUnidad('Unidad');
        $participante->setCorreoAux('correo.antiguo@example.com'); // Valor inicial para Verificamos cambio
        $participante->setUsuario($usuario);

        $this->manager->persist($participante);
        $this->manager->flush();
        $this->manager->refresh($usuario);

        $this->client->loginUser($usuario);

        $this->client->request('GET', '/intranet/forpas/participante/mis-datos');
        $this->client->submitForm('Actualizar', [
            'participante_contacto[correo_aux]' => 'correo.nuevo@example.com',
            'participante_contacto[telefono_trabajo]' => '987654321',
        ]);

        // Verificamos la redirección
        $this->assertResponseRedirects('/intranet/forpas/participante');
        $this->client->followRedirect();

        // Verificamos que los datos se han actualizado correctamente
        $participanteActualizado = $this->manager->getRepository(Participante::class)->find($participante->getId());
        $this->assertEquals('correo.nuevo@example.com', $participanteActualizado->getCorreoAux());
        $this->assertEquals('987654321', $participanteActualizado->getTelefonoTrabajo());
    }
    public function testFichaFormativaAccesoDenegadoSinAutenticacion(): void
    {
        $this->client->request('GET', '/intranet/forpas/participante/ficha-formativa');

        // Verificamos que el usuario no autenticado es redirigido a la página de login
        $this->assertResponseRedirects('/intranet/login');
    }
    public function testFichaFormativaAccesoDenegadoSinParticipante(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER'); // Usuario sin Participante asociado
        $this->client->loginUser($usuario);

        $this->client->request('GET', '/intranet/forpas/participante/ficha-formativa');

        // Verificamos que el usuario sin Participante es redirigido a la página de inicio
        $this->assertResponseRedirects('/intranet/forpas/');
    }
    public function testFichaFormativaDatosCargadosCorrectamente(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');

        // Creamos un Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24003');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(0);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos ediciones asociadas al Curso
        $proximaEdicion = new Edicion();
        $proximaEdicion->setCodigoEdicion('24003/01');
        $proximaEdicion->setEstado(0);
        $proximaEdicion->setSesiones(3);
        $proximaEdicion->setMaxParticipantes(30);
        $proximaEdicion->setFechaInicio(new DateTime('+10 days'));
        $proximaEdicion->setCurso($curso);
        $curso->addEdiciones($proximaEdicion);
        $this->manager->persist($proximaEdicion);

        $certificadaEdicion = new Edicion();
        $certificadaEdicion->setCodigoEdicion('24003/02');
        $certificadaEdicion->setEstado(1);
        $certificadaEdicion->setSesiones(3);
        $certificadaEdicion->setMaxParticipantes(30);
        $certificadaEdicion->setFechaInicio(new DateTime('-30 days'));
        $certificadaEdicion->setFechaFin(new DateTime('-10 days'));
        $certificadaEdicion->setCurso($curso);
        $curso->addEdiciones($certificadaEdicion);
        $this->manager->persist($certificadaEdicion);

        $otraEdicion = new Edicion();
        $otraEdicion->setCodigoEdicion('24003/03');
        $otraEdicion->setEstado(2);
        $otraEdicion->setSesiones(3);
        $otraEdicion->setMaxParticipantes(30);
        $otraEdicion->setFechaInicio(new DateTime('-60 days'));
        $otraEdicion->setFechaFin(new DateTime('-40 days'));
        $otraEdicion->setCurso($curso);
        $curso->addEdiciones($otraEdicion);
        $this->manager->persist($otraEdicion);

        // Creamos un Participante
        $participante = new Participante();
        $participante->setNif('12345678A');
        $participante->setNombre('John');
        $participante->setApellidos('Doe');
        $participante->setUnidad('Unidad');
        $participante->setUsuario($usuario);
        $this->manager->persist($participante);

        // Creamos un ParticipanteEdicion
        $participanteEdicion1 = new ParticipanteEdicion();
        $participanteEdicion1->setFechaSolicitud(new DateTime('2024-01-01'));
        $participanteEdicion1->setCertificado(null);
        $participanteEdicion1->setLibro(null);
        $participanteEdicion1->setNumeroTitulo(null);
        $participanteEdicion1->setObservaciones('Próxima edición');
        $participanteEdicion1->setApto(null);
        $participanteEdicion1->setParticipante($participante);
        $participanteEdicion1->setEdicion($proximaEdicion);
        $this->manager->persist($participanteEdicion1);

        $participanteEdicion2 = new ParticipanteEdicion();
        $participanteEdicion2->setFechaSolicitud(new DateTime('2023-12-01'));
        $participanteEdicion2->setCertificado('S');
        $participanteEdicion2->setLibro(2024);
        $participanteEdicion2->setNumeroTitulo(1);
        $participanteEdicion2->setObservaciones('Edición certificada');
        $participanteEdicion2->setApto(1);
        $participanteEdicion2->setParticipante($participante);
        $participanteEdicion2->setEdicion($certificadaEdicion);
        $this->manager->persist($participanteEdicion2);

        $participanteEdicion3 = new ParticipanteEdicion();
        $participanteEdicion3->setFechaSolicitud(new DateTime('2023-11-01'));
        $participanteEdicion3->setCertificado(null);
        $participanteEdicion3->setLibro(null);
        $participanteEdicion3->setNumeroTitulo(null);
        $participanteEdicion3->setObservaciones('Otra edición');
        $participanteEdicion3->setApto(0);
        $participanteEdicion3->setParticipante($participante);
        $participanteEdicion3->setEdicion($otraEdicion);
        $this->manager->persist($participanteEdicion3);

        // Persistimos los datos en la base de datos
        $this->manager->flush();
        $this->manager->refresh($usuario);

        // Probamos la funcionalidad
        $this->client->loginUser($usuario);
        $crawler = $this->client->request('GET', '/intranet/forpas/participante/ficha-formativa');

        $this->assertResponseIsSuccessful();

        // Verificamos las tablas
        $this->assertSelectorExists('table#tablaProximas');
        $this->assertSelectorExists('table#tablaCertificadas');
        $this->assertSelectorExists('table#tablaOtras');
    }
    public function testListarCursosMuestraSoloCursosDelAnoActual(): void
    {
        // Creamos un usuario y lo autenticamos
        $usuario = $this->createUserWithRole('ROLE_USER');
        $this->client->loginUser($usuario);

        // Año actual y su código
        $currentYear = date('Y');
        $currentYearCode = substr($currentYear, -2);

        // Curso del año actual
        $cursoActual = new Curso();
        $cursoActual->setCodigoCurso($currentYearCode . '001');
        $cursoActual->setNombreCurso('Curso Actual');
        $cursoActual->setHoras(10);
        $cursoActual->setEdicionesEstimadas(2);
        $cursoActual->setParticipantesEdicion(20);
        $cursoActual->setVisibleWeb(true);
        $cursoActual->setHorasVirtuales(5);
        $cursoActual->setCalificable(true);
        $this->manager->persist($cursoActual);

        // Curso de otro año
        $cursoAnterior = new Curso();
        $cursoAnterior->setCodigoCurso('23002'); // Código que no corresponde al año actual
        $cursoAnterior->setNombreCurso('Curso Anterior');
        $cursoAnterior->setHoras(15);
        $cursoAnterior->setEdicionesEstimadas(1);
        $cursoAnterior->setParticipantesEdicion(10);
        $cursoAnterior->setVisibleWeb(false);
        $cursoAnterior->setHorasVirtuales(0);
        $cursoAnterior->setCalificable(false);
        $this->manager->persist($cursoAnterior);

        // Persistimos los datos en la base de datos
        $this->manager->flush();

        // Realizamos la solicitud
        $crawler = $this->client->request('GET', '/intranet/forpas/participante/cursos');

        // Verificamos una respuesta exitosa
        $this->assertResponseIsSuccessful();

        // Verificamos que solo el curso del año actual aparece en la tabla
        $this->assertSelectorTextContains('table#datosCursos tbody tr:first-child td:nth-child(2)', 'Curso Actual');
        $this->assertSelectorTextContains('table#datosCursos tbody tr:first-child td:nth-child(3)', '10');
        $this->assertSelectorTextContains('table#datosCursos tbody tr:first-child td:nth-child(4)', '5');
        $this->assertSelectorExists('table#datosCursos tbody tr:first-child td:nth-child(5) sup.text-success.fas.fa-check');

        // Verificamos que el curso de otro año no aparece
        $this->assertSelectorTextNotContains('table#datosCursos', 'Curso Anterior');
    }
    public function testShowCursoMuestraDatosCorrectamente(): void
    {
        // Creamos un usuario y lo autenticamos
        $usuario = $this->createUserWithRole('ROLE_USER');
        $this->client->loginUser($usuario);

        // Creamos un curso de ejemplo
        $curso = new Curso();
        $curso->setCodigoCurso('24001');
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setHoras(20);
        $curso->setHorasVirtuales(5);
        $curso->setCalificable(true);
        $curso->setEdicionesEstimadas(3);
        $curso->setParticipantesEdicion(30);
        $curso->setVisibleWeb(true);
        $curso->setContenidos("Contenido del curso");
        $curso->setDestinatarios("Destinatarios del curso");
        $curso->setRequisitos("Requisitos del curso");
        $curso->setObjetivos("Objetivos del curso");
        $curso->setJustificacion("Justificación del curso");
        $curso->setPlazoSolicitud("Plazo de solicitud");
        $curso->setCoordinador("Coordinador del curso");
        $curso->setObservaciones("Observaciones del curso");

        // Persistimos el curso en la base de datos
        $this->manager->persist($curso);
        $this->manager->flush();

        // Realizamos la solicitud
        $crawler = $this->client->request('GET', "/intranet/forpas/participante/cursos/{$curso->getId()}");

        // Verificamos que la respuesta es exitosa
        $this->assertResponseIsSuccessful();

        // Verificamos que los datos del curso se muestran correctamente
        $this->assertSelectorTextContains('.row:nth-child(1) .fila-valor', '24001'); // Código del curso
        $this->assertSelectorTextContains('.row:nth-child(2) .fila-valor', 'Curso de Prueba'); // Nombre del curso
        $this->assertSelectorTextContains('.row:nth-child(3) .fila-valor', '20'); // Horas totales
        $this->assertSelectorTextContains('.row:nth-child(4) .fila-valor', '5'); // Horas virtuales
        $this->assertSelectorExists('.row:nth-child(5) .fila-valor sup.text-success.fas.fa-check'); // Evaluable
        $this->assertSelectorTextContains('.row:nth-child(6) .fila-valor', '3'); // Ediciones estimadas
        $this->assertSelectorTextContains('.row:nth-child(7) .fila-valor', '30'); // Participantes edición
        $this->assertSelectorExists('.row:nth-child(8) .fila-valor sup.text-success.fas.fa-check'); // Visible web
    }
    public function testListarEdicionesDevuelveDatosCorrectamente(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');

        // Creamos un participante y asociarlo al usuario
        $participante = new Participante();
        $participante->setNif('12345678A');
        $participante->setNombre('John');
        $participante->setApellidos('Doe');
        $participante->setUnidad('Unidad');
        $participante->setUsuario($usuario);
        $usuario->setParticipante($participante);

        $this->manager->persist($participante);
        $this->manager->persist($usuario);
        $this->manager->flush();
        $this->manager->refresh($usuario);

        // Verificamos que el participante está asociado
        $this->assertNotNull($usuario->getParticipante(), 'El usuario debería tener un participante asociado.');

        // Creamos un curso
        $curso = new Curso();
        $curso->setCodigoCurso('24001');
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setHoras(20);
        $curso->setHorasVirtuales(5);
        $curso->setCalificable(true);
        $curso->setEdicionesEstimadas(2);
        $curso->setParticipantesEdicion(30);
        $curso->setVisibleWeb(true);
        $this->manager->persist($curso);

        // Creamos ediciones asociadas al curso
        $edicion1 = new Edicion();
        $edicion1->setCodigoEdicion('24001/01');
        $edicion1->setFechaInicio(new \DateTime('+10 days'));
        $edicion1->setHorario('08:00-14:00');
        $edicion1->setLugar('Aula 1');
        $edicion1->setEstado(1);
        $edicion1->setSesiones(3);
        $edicion1->setMaxParticipantes(30);
        $edicion1->setCurso($curso);
        $this->manager->persist($edicion1);

        $edicion2 = new Edicion();
        $edicion2->setCodigoEdicion('24001/02');
        $edicion2->setFechaInicio(new \DateTime('+20 days'));
        $edicion2->setHorario('09:00-15:00');
        $edicion2->setLugar('Aula 2');
        $edicion2->setEstado(1);
        $edicion2->setSesiones(3);
        $edicion2->setMaxParticipantes(30);
        $edicion2->setCurso($curso);
        $this->manager->persist($edicion2);

        // Persistimos en la base de datos
        $this->manager->flush();

        // Autenticamos al usuario
        $this->client->loginUser($usuario);

        // Comprobación explícita
        $this->assertNotNull($usuario->getParticipante(), 'El usuario debería tener un participante asociado.');
    }


    /**
     * @throws ORMException
     */
    public function testRealizarInscripcionValida(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');

        // Creamos participante asociado al usuario
        $participante = new Participante();
        $participante->setNif('12345678A');
        $participante->setNombre('John');
        $participante->setApellidos('Doe');
        $participante->setUnidad('Unidad');
        $participante->setUsuario($usuario);
        $this->manager->persist($participante);

        // Creamos curso y edición
        $curso = new Curso();
        $curso->setCodigoCurso('24001');
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setHoras(20);
        $curso->setHorasVirtuales(5);
        $curso->setCalificable(true);
        $curso->setEdicionesEstimadas(2);
        $curso->setParticipantesEdicion(30);
        $curso->setVisibleWeb(true);
        $this->manager->persist($curso);

        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24001/01');
        $edicion->setFechaInicio(new \DateTime('+10 days'));
        $edicion->setHorario('08:00-14:00');
        $edicion->setLugar('Aula 1');
        $edicion->setEstado(1);
        $edicion->setSesiones(3);
        $edicion->setMaxParticipantes(30);
        $edicion->setCurso($curso);
        $this->manager->persist($edicion);

        $this->manager->flush();
        $this->manager->refresh($usuario);

        // Autenticamos al usuario y realizar solicitud
        $this->client->loginUser($usuario);
        $this->client->request('GET', "/intranet/forpas/participante/inscripcion/realizar/{$edicion->getId()}");

        // Verificamos redirección y persistencia de la inscripción
        $this->assertResponseRedirects('/intranet/forpas/participante');
        $this->assertNotNull(
            $this->manager->getRepository(ParticipanteEdicion::class)->findOneBy([
                'participante' => $participante,
                'edicion' => $edicion,
            ]),
            'La inscripción debería haberse creado correctamente.'
        );
    }
    public function testCancelarInscripcionValida(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');

        // Creamos participante y curso
        $participante = new Participante();
        $participante->setNif('12345678A');
        $participante->setNombre('John');
        $participante->setApellidos('Doe');
        $participante->setUnidad('Unidad');
        $participante->setUsuario($usuario);
        $this->manager->persist($participante);

        $curso = new Curso();
        $curso->setCodigoCurso('24001');
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setHoras(20);
        $curso->setHorasVirtuales(5);
        $curso->setCalificable(true);
        $curso->setEdicionesEstimadas(2);
        $curso->setParticipantesEdicion(30);
        $curso->setVisibleWeb(true);
        $this->manager->persist($curso);

        // Creamos edición e inscripción
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24001/01');
        $edicion->setFechaInicio(new \DateTime('+10 days'));
        $edicion->setHorario('08:00-14:00');
        $edicion->setLugar('Aula 1');
        $edicion->setEstado(1);
        $edicion->setSesiones(3);
        $edicion->setMaxParticipantes(30);
        $edicion->setCurso($curso);
        $this->manager->persist($edicion);

        $inscripcion = new ParticipanteEdicion();
        $inscripcion->setParticipante($participante);
        $inscripcion->setEdicion($edicion);
        $inscripcion->setFechaSolicitud(new \DateTime());
        $this->manager->persist($inscripcion);

        $this->manager->flush();
        $this->manager->refresh($usuario);

        // Autenticamos al usuario y realizamos cancelación
        $this->client->loginUser($usuario);
        $this->client->request('GET', "/intranet/forpas/participante/inscripcion/cancelar/{$edicion->getId()}");

        // Verificamos redirección y eliminación de la inscripción
        $this->assertResponseRedirects('/intranet/forpas/participante');
        $this->assertNull(
            $this->manager->getRepository(ParticipanteEdicion::class)->findOneBy([
                'participante' => $participante,
                'edicion' => $edicion,
            ]),
            'La inscripción debería haberse cancelado correctamente.'
        );
    }
    public function testCambiarInscripcionValida(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');

        // Creamos participante, curso y ediciones
        $participante = new Participante();
        $participante->setNif('12345678A');
        $participante->setNombre('John');
        $participante->setApellidos('Doe');
        $participante->setUnidad('Unidad');
        $participante->setUsuario($usuario);
        $this->manager->persist($participante);

        $curso = new Curso();
        $curso->setCodigoCurso('24001');
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setHoras(20);
        $curso->setHorasVirtuales(5);
        $curso->setCalificable(true);
        $curso->setEdicionesEstimadas(2);
        $curso->setParticipantesEdicion(30);
        $curso->setVisibleWeb(true);
        $this->manager->persist($curso);

        $edicionActual = new Edicion();
        $edicionActual->setCodigoEdicion('24001/01');
        $edicionActual->setFechaInicio(new \DateTime('+10 days'));
        $edicionActual->setHorario('08:00-14:00');
        $edicionActual->setLugar('Aula 1');
        $edicionActual->setEstado(1);
        $edicionActual->setSesiones(3);
        $edicionActual->setMaxParticipantes(30);
        $edicionActual->setCurso($curso);
        $this->manager->persist($edicionActual);

        $nuevaEdicion = new Edicion();
        $nuevaEdicion->setCodigoEdicion('24001/02');
        $nuevaEdicion->setFechaInicio(new \DateTime('+20 days'));
        $nuevaEdicion->setHorario('09:00-15:00');
        $nuevaEdicion->setLugar('Aula 2');
        $nuevaEdicion->setEstado(1);
        $nuevaEdicion->setSesiones(3);
        $nuevaEdicion->setMaxParticipantes(30);
        $nuevaEdicion->setCurso($curso);
        $this->manager->persist($nuevaEdicion);

        $inscripcion = new ParticipanteEdicion();
        $inscripcion->setParticipante($participante);
        $inscripcion->setEdicion($edicionActual);
        $inscripcion->setFechaSolicitud(new \DateTime());
        $this->manager->persist($inscripcion);

        $this->manager->flush();
        $this->manager->refresh($usuario);

        // Autenticamos al usuario y realizamos cambio de inscripción
        $this->client->loginUser($usuario);
        $this->client->request('GET', "/intranet/forpas/participante/inscripcion/cambiar/{$nuevaEdicion->getId()}");

        // Verificamos redirección y realizamos un cambio de la inscripción
        $this->assertResponseRedirects('/intranet/forpas/participante');
        $this->assertNull(
            $this->manager->getRepository(ParticipanteEdicion::class)->findOneBy([
                'participante' => $participante,
                'edicion' => $edicionActual,
            ]),
            'La inscripción anterior debería haberse eliminado.'
        );
        $this->assertNotNull(
            $this->manager->getRepository(ParticipanteEdicion::class)->findOneBy([
                'participante' => $participante,
                'edicion' => $nuevaEdicion,
            ]),
            'La nueva inscripción debería haberse creado correctamente.'
        );
    }

    public function testListarProximasEdicionesMuestraEdicionesCorrectas(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');

        // Creamos participante asociado al usuario
        $participante = new Participante();
        $participante->setNif('12345678A');
        $participante->setNombre('John');
        $participante->setApellidos('Doe');
        $participante->setUnidad('Unidad');
        $participante->setUsuario($usuario);
        $this->manager->persist($participante);

        // Creamos curso y ediciones
        $curso = new Curso();
        $curso->setCodigoCurso('24001');
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setHoras(20);
        $curso->setHorasVirtuales(5);
        $curso->setCalificable(true);
        $curso->setEdicionesEstimadas(2);
        $curso->setParticipantesEdicion(30);
        $curso->setVisibleWeb(true);
        $this->manager->persist($curso);

        $edicion1 = new Edicion();
        $edicion1->setCodigoEdicion('24001/01');
        $edicion1->setFechaInicio(new \DateTime('+10 days'));
        $edicion1->setFechaFin(new \DateTime('+20 days'));
        $edicion1->setHorario('08:00-14:00');
        $edicion1->setLugar('Aula 1');
        $edicion1->setEstado(1);
        $edicion1->setSesiones(3);
        $edicion1->setMaxParticipantes(30);
        $edicion1->setCurso($curso);
        $this->manager->persist($edicion1);

        $edicion2 = new Edicion();
        $edicion2->setCodigoEdicion('24001/02');
        $edicion2->setFechaInicio(new \DateTime('+30 days'));
        $edicion2->setFechaFin(new \DateTime('+40 days'));
        $edicion2->setHorario('09:00-15:00');
        $edicion2->setLugar('Aula 2');
        $edicion2->setEstado(1);
        $edicion2->setSesiones(3);
        $edicion2->setMaxParticipantes(30);
        $edicion2->setCurso($curso);
        $this->manager->persist($edicion2);

        // Creamos una edición pasada que no debería mostrarse
        $edicionPasada = new Edicion();
        $edicionPasada->setCodigoEdicion('24001/03');
        $edicionPasada->setFechaInicio(new \DateTime('-30 days'));
        $edicionPasada->setFechaFin(new \DateTime('-20 days'));
        $edicionPasada->setHorario('10:00-16:00');
        $edicionPasada->setLugar('Aula 3');
        $edicionPasada->setEstado(1);
        $edicionPasada->setSesiones(3);
        $edicionPasada->setMaxParticipantes(30);
        $edicionPasada->setCurso($curso);
        $this->manager->persist($edicionPasada);

        $this->manager->flush();
        $this->manager->refresh($usuario);

        // Autenticamos al usuario y realizamos una solicitud
        $this->client->loginUser($usuario);
        $crawler = $this->client->request('GET', '/intranet/forpas/participante/proximas-ediciones');

        // Verificamos que la respuesta es exitosa
        $this->assertResponseIsSuccessful();

        // Verificamos que las ediciones futuras aparecen en la tabla
        $this->assertSelectorTextContains('table#datosProximasEdiciones tbody tr:nth-child(1) td:nth-child(1)', '24001/01');
        $this->assertSelectorTextContains('table#datosProximasEdiciones tbody tr:nth-child(2) td:nth-child(1)', '24001/02');

        // Verificamos que la edición pasada no aparece en la tabla
        $this->assertSelectorTextNotContains('table#datosProximasEdiciones tbody', '24001/03');

        // Verificamos que el número de filas corresponde a las ediciones futuras
        $this->assertCount(2, $crawler->filter('table#datosProximasEdiciones tbody tr'));
    }
}