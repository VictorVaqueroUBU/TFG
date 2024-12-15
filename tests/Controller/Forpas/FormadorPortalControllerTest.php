<?php
namespace App\Tests\Controller\Forpas;

use App\Entity\Forpas\Asistencia;
use App\Entity\Forpas\Curso;
use App\Entity\Forpas\FormadorEdicion;
use App\Entity\Forpas\Participante;
use App\Entity\Forpas\ParticipanteEdicion;
use App\Entity\Forpas\Sesion;
use DateTime;
use App\Entity\Forpas\Edicion;
use App\Entity\Forpas\Formador;
use Doctrine\ORM\Tools\SchemaTool;

class FormadorPortalControllerTest extends BaseControllerTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = static::getContainer()->get('doctrine')->getManager();
        // Limpieza completa de la base de datos
        $schemaTool = new SchemaTool($this->manager);
        $classes = $this->manager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);
    }
    public function testMisDatosActualizacionExitosa(): void
    {
        $usuario = $this->createUserWithRole('ROLE_TEACHER');

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
        $usuario->setFormador($formador);

        $this->manager->persist($usuario);
        $this->manager->persist($formador);
        $this->manager->flush();

        $this->client->loginUser($usuario);

        // Realizamos la petición GET a la ruta 'mis_datos'
        $crawler = $this->client->request('GET', '/intranet/forpas/formador/mis-datos');

        // Verificamos que la respuesta es exitosa
        $this->assertResponseIsSuccessful();

        // Seleccionamos el formulario
        $form = $crawler->selectButton('Actualizar')->form();

        // Modificamos los datos del formador
        $form['formador_contacto[correo_aux]'] = 'nuevo.contacto@example.com';

        // Enviamos el formulario
        $this->client->submit($form);

        // Verificamos la redirección a la ruta 'intranet_forpas_formador'
        $this->assertResponseRedirects('/intranet/forpas/formador', 303, 'Debería redirigir al portal del formador después de la actualización.');

        // Seguimos la redirección
        $this->client->followRedirect();

        // Verificamos el mensaje flash de éxito
        $this->assertSelectorTextContains('.alert-success', 'Tus datos de contacto se han actualizado correctamente.');

        // Recuperamos la entidad actualizada desde la base de datos
        $formadorActualizado = $this->manager->getRepository(Formador::class)->findOneBy(['nif' => 'FIND-TEST']);
        $this->assertNotNull($formadorActualizado, 'El formador debería existir en la base de datos.');
        $this->assertEquals('nuevo.contacto@example.com', $formadorActualizado->getCorreoAux(), 'El contacto del formador debería haberse actualizado.');
    }
    public function testAccessDenied(): void
    {
        $usuario = $this->createUserWithRole('ROLE_TEACHER');
        $this->manager->flush();

        $this->client->loginUser($usuario);
        $this->client->request('GET', '/intranet/forpas/formador/mis-datos');

        // Si la configuración actual redirige en lugar de mostrar un 403
        $this->assertResponseRedirects('/intranet/forpas/');
    }

    public function testEdicionesConAsignaciones(): void
    {
        $usuario = $this->createUserWithRole('ROLE_TEACHER');

        // Creamos una entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24501');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(0);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos una entidad Edicion futura (Abierta)
        $edicion1 = new Edicion();
        $edicion1->setCodigoEdicion('EA123');
        $edicion1->setEstado(0); // Abierta
        $edicion1->setFechaInicio(new DateTime('+1 day'));
        $edicion1->setSesiones(2);
        $edicion1->setMaxParticipantes(20);
        $edicion1->setCurso($curso);
        $this->manager->persist($edicion1);

        // Creamos una entidad Edicion pasada (Cerrada)
        $edicion2 = new Edicion();
        $edicion2->setCodigoEdicion('EC123');
        $edicion2->setEstado(1); // Cerrada
        $edicion2->setFechaInicio(new DateTime('-1 day'));
        $edicion2->setSesiones(2);
        $edicion2->setMaxParticipantes(20);
        $edicion2->setCurso($curso);
        $this->manager->persist($edicion2);

        // Creamos una entidad Formador
        $formador = new Formador();
        $formador->setNif('12121212A');
        $formador->setNombre('John');
        $formador->setApellidos('Doe');
        $formador->setOrganizacion('Value3');
        $formador->setCorreoAux('aux@example.com'); // Ajusta el correo
        $formador->setTelefono('123456789'); // Ajusta el teléfono
        $formador->setObservaciones('Observaciones de prueba');
        $formador->setFormadorRJ(1);

        $formador->setUsuario($usuario);
        $usuario->setFormador($formador);

        $this->manager->persist($usuario);
        $this->manager->persist($formador);

        // Asignaciones
        $formadorEdicion1 = new FormadorEdicion();
        $formadorEdicion1->setEdicion($edicion1);
        $formadorEdicion1->setFormador($formador);
        $this->manager->persist($formadorEdicion1);

        $formadorEdicion2 = new FormadorEdicion();
        $formadorEdicion2->setEdicion($edicion2);
        $formadorEdicion2->setFormador($formador);
        $this->manager->persist($formadorEdicion2);

        $this->manager->flush();

        $this->client->loginUser($usuario);

        // Solicitamos la página
        $crawler = $this->client->request('GET', '/intranet/forpas/formador/mis-ediciones');

        // Verificamos respuesta
        $this->assertResponseIsSuccessful();

        // Verificamos pestañas
        $this->assertSelectorTextContains('button#abiertas-tab', 'Ediciones Abiertas');
        $this->assertSelectorTextContains('button#cerradas-tab', 'Ediciones Cerradas');

        // Verificamos tablas
        $this->assertSelectorExists('#tablaAbiertas');
        $this->assertSelectorExists('#tablaCerradas');

        // Verificamos contenido de la tabla abierta
        $this->assertSelectorTextContains('#tablaAbiertas tbody tr td', 'EA123');

        // Verificamos contenido de la tabla cerrada
        $this->assertSelectorTextContains('#tablaCerradas tbody tr td', 'EC123');
    }
    public function testEdicionShow(): void
    {
        $usuario = $this->createUserWithRole('ROLE_TEACHER');
        $usuario2 = $this->createUserWithRole('ROLE_USER');

        // Creamos una entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24501');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(0);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos una entidad Edicion (Abierta)
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24501/01');
        $edicion->setEstado(0); // Abierta
        $edicion->setFechaInicio(new DateTime('+1 day'));
        $edicion->setFechaFin(new DateTime('+1 day'));
        $edicion->setSesiones(2);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);

        // Creamos una entidad Formador
        $formador = new Formador();
        $formador->setNif('12121212A');
        $formador->setNombre('John');
        $formador->setApellidos('Doe');
        $formador->setOrganizacion('Value3');
        $formador->setCorreoAux('aux@example.com');
        $formador->setTelefono('123456789');
        $formador->setObservaciones('Observaciones de prueba');
        $formador->setFormadorRJ(1);
        $formador->setUsuario($usuario);
        $this->manager->persist($formador);

        $usuario->setFormador($formador);
        $this->manager->persist($usuario);

        // Creamos una entidad Participante
        $participante = new Participante();
        $participante->setNif('12345678A');
        $participante->setNombre('Johnny');
        $participante->setApellidos('Lopez');
        $participante->setUnidad('Unidad');
        $participante->setUsuario($usuario2);
        $this->manager->persist($participante);

        // Creamos sesiones
        $sesion = new Sesion();
        $sesion->setEdicion($edicion);
        $sesion->setFormador($formador);
        $sesion->setFecha(new DateTime('+1 day'));
        $sesion->setHoraInicio(new DateTime('09:00'));
        $sesion->setDuracion(120);
        $sesion->setTipo(0);
        $this->manager->persist($sesion);

        $edicion->addSesionesEdicion($sesion);
        $this->manager->persist($edicion);

        // Creamos asistencias
        $asistencia = new Asistencia();
        $asistencia->setSesion($sesion);
        $asistencia->setFormador($formador);
        $asistencia->setParticipante($participante);
        $asistencia->setAsiste(true);
        $asistencia->setJustifica(false);
        $this->manager->persist($asistencia);

        $this->manager->flush();
        $this->client->loginUser($usuario);

        // Hacemos la petición a la ruta
        $this->client->request('GET', '/intranet/forpas/formador/mis-ediciones/' . $edicion->getId());

        $this->assertNotNull($sesion->getId(), 'El ID de la sesión no debe ser null.');

        // Verificamos respuesta
        $this->assertResponseIsSuccessful();

        // Verificamos que los datos de la edición están presentes
        $this->assertSelectorTextContains('.fila-valor', '24501/01: Curso de Prueba');

        // Verificamos existencia de la tabla de sesiones
        $this->assertSelectorExists('#datosSesiones');

        // Obtenemos la URL generada por Symfony para la ruta sesion_fillIn
        $urlAsistencia = $this->getContainer()->get('router')->generate('intranet_forpas_formador_sesion_fillIn', ['id' => $sesion->getId()]);

        // Verificamos que el enlace existe con el href correcto
        $this->assertSelectorExists('a[href="' . $urlAsistencia . '"][title="Asistencia"]');
    }
    public function testRemitirDatos(): void
    {
        $usuario = $this->createUserWithRole('ROLE_TEACHER');

        // Creamos una entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24501');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(0);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos una entidad Edicion (Abierta)
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24501/01');
        $edicion->setEstado(0); // Abierta
        $edicion->setFechaInicio(new DateTime('+1 day'));
        $edicion->setFechaFin(new DateTime('+1 day'));
        $edicion->setSesiones(2);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);
        $this->manager->persist($edicion);

        // Crear Formador
        $formador = new Formador();
        $formador->setNif('12345678A');
        $formador->setNombre('John');
        $formador->setApellidos('Doe');
        $formador->setOrganizacion('USE');
        $formador->setUsuario($usuario);
        $this->manager->persist($formador);

        $usuario->setFormador($formador);
        $this->manager->persist($usuario);

        // Asociar Formador con la Edicion a través de FormadorEdicion
        $formadorEdicion = new FormadorEdicion();
        $formadorEdicion->setFormador($formador);
        $formadorEdicion->setEdicion($edicion);
        $this->manager->persist($formadorEdicion);

        $this->manager->flush();

        // Loguear el usuario
        $this->client->loginUser($usuario);

        // Realizar la petición POST
        $this->client->request('POST', '/intranet/forpas/formador/mis-ediciones/' . $edicion->getId() . '/remitir');

        // Redirección y recarga de entidad
        $this->assertResponseRedirects(
            $this->getContainer()->get('router')->generate('intranet_forpas_formador_mis_ediciones'),
            302
        );

        $this->manager->clear(); // Limpiar el EntityManager
        $edicionActualizada = $this->manager->getRepository(Edicion::class)->find($edicion->getId());

        // Verificar estado cambiado
        $this->assertSame(1, $edicionActualizada->getEstado());

        // Seguir la redirección
        $this->client->followRedirect();

        // Verificar mensaje flash
        $this->assertSelectorTextContains('.alert-success', 'Los datos de la edición han sido remitidos correctamente.');

        // Verificar que la tabla de ediciones cerradas existe
        $this->assertSelectorExists('#tablaCerradas', 'La tabla de ediciones cerradas debe estar presente.');
    }
    public function testSesionNewCaso1(): void
    {
        $usuario = $this->createUserWithRole('ROLE_TEACHER');

        $usuario = $this->createUserWithRole('ROLE_TEACHER');

        // Creamos una entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24501');
        $curso->setHoras(2);
        $curso->setHorasVirtuales(0);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos una entidad Edicion (Abierta)
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24501/01');
        $edicion->setEstado(0); // Abierta
        $edicion->setFechaInicio(new DateTime('+1 day'));
        $edicion->setFechaFin(new DateTime('+1 day'));
        $edicion->setSesiones(1);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);
        $this->manager->persist($edicion);

        // Crear Formador
        $formador = new Formador();
        $formador->setNif('12345678A');
        $formador->setNombre('John');
        $formador->setApellidos('Doe');
        $formador->setOrganizacion('USE');
        $formador->setUsuario($usuario);
        $this->manager->persist($formador);

        $usuario->setFormador($formador);
        $this->manager->persist($usuario);

        $this->manager->flush();

        // Loguear el usuario
        $this->client->loginUser($usuario);

        // ---- CASO 1: Crear sesión correctamente ----
        $crawler = $this->client->request(
            'GET',
            '/intranet/forpas/formador/sesion-new/' . $edicion->getId()
        );
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Guardar')->form([
            'sesion[fecha]' => (new DateTime('+1 day'))->format('Y-m-d'),
            'sesion[hora_inicio]' => '09:00',
            'sesion[duracionHoras]' => 2, // Horas
            'sesion[duracionMinutos]' => 0, // Minutos
            'sesion[tipo]' => 0, // Presencial
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects(
            $this->getContainer()->get('router')->generate(
                'intranet_forpas_formador_mis_ediciones_show',
                ['id' => $edicion->getId()]
            )
        );

        $this->manager->clear(); // Limpiar el estado del EntityManager
        $edicion = $this->manager->getRepository(Edicion::class)->find($edicion->getId());

        $this->assertCount(1, $edicion->getSesionesEdicion(), 'Se debe haber creado una sesión.');
    }
    public function testSesionNewCaso2(): void
    {
        $usuario = $this->createUserWithRole('ROLE_TEACHER');

        // Creamos una entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24502');
        $curso->setHoras(2);
        $curso->setHorasVirtuales(0);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos una entidad Edicion (Abierta)
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24502/01');
        $edicion->setEstado(0); // Abierta
        $edicion->setFechaInicio(new DateTime('+1 day'));
        $edicion->setFechaFin(new DateTime('+1 day'));
        $edicion->setSesiones(1);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);
        $this->manager->persist($edicion);

        // Crear Formador
        $formador = new Formador();
        $formador->setNif('12345678A');
        $formador->setNombre('John');
        $formador->setApellidos('Doe');
        $formador->setOrganizacion('USE');
        $formador->setUsuario($usuario);
        $this->manager->persist($formador);

        $usuario->setFormador($formador);
        $this->manager->persist($usuario);

        $this->manager->flush();

        // Loguear el usuario
        $this->client->loginUser($usuario);

        // ---- CASO 2: Superar horas totales del curso ----
        $crawler = $this->client->request(
            'GET',
            '/intranet/forpas/formador/sesion-new/' . $edicion->getId()
        );

        $form = $crawler->selectButton('Guardar')->form([
            'sesion[fecha]' => (new DateTime('+1 day'))->format('Y-m-d'),
            'sesion[hora_inicio]' => '09:00',
            'sesion[duracionHoras]' => 20, // Horas
            'sesion[duracionMinutos]' => 0, // Minutos
            'sesion[tipo]' => 0, // Presencial
        ]);

        $this->client->submit($form);

        // Verifica si la respuesta redirige a la URL esperada
        $response = $this->client->getResponse();
        $this->assertResponseRedirects(
            $this->getContainer()->get('router')->generate(
                'intranet_forpas_formador_mis_ediciones_show',
                ['id' => $edicion->getId()]
            )
        );

        // Inspecciona el contenido antes del followRedirect
        $this->client->followRedirect();

        // Verifica el contenido después de la redirección
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertStringContainsString(
            'Sesión no grabada. Con los datos introducidos se superan las horas totales del curso.',
            $responseContent
        );
    }
    public function testSesionNewCaso3(): void
    {
        $usuario = $this->createUserWithRole('ROLE_TEACHER');

        // Creamos una entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24503');
        $curso->setHoras(20);
        $curso->setHorasVirtuales(2);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos una entidad Edicion (Abierta)
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24503/01');
        $edicion->setEstado(0); // Abierta
        $edicion->setFechaInicio(new DateTime('+1 day'));
        $edicion->setFechaFin(new DateTime('+1 day'));
        $edicion->setSesiones(1);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);
        $this->manager->persist($edicion);

        // Crear Formador
        $formador = new Formador();
        $formador->setNif('12345678A');
        $formador->setNombre('John');
        $formador->setApellidos('Doe');
        $formador->setOrganizacion('USE');
        $formador->setUsuario($usuario);
        $this->manager->persist($formador);

        $usuario->setFormador($formador);
        $this->manager->persist($usuario);

        $this->manager->flush();

        // Loguear el usuario
        $this->client->loginUser($usuario);

        // ---- CASO 3: Superar horas virtuales del curso ----
        $crawler = $this->client->request(
            'GET',
            '/intranet/forpas/formador/sesion-new/' . $edicion->getId()
        );

        $form = $crawler->selectButton('Guardar')->form([
            'sesion[fecha]' => (new DateTime('+1 day'))->format('Y-m-d'),
            'sesion[hora_inicio]' => '09:00',
            'sesion[duracionHoras]' => 10, // Horas
            'sesion[duracionMinutos]' => 0, // Minutos
            'sesion[tipo]' => 1, // Virtual
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects(
            $this->getContainer()->get('router')->generate(
                'intranet_forpas_formador_mis_ediciones_show',
                ['id' => $edicion->getId()]
            )
        );
        $this->client->followRedirect();
        $this->assertSelectorTextContains(
            '.alert-warning',
            'Sesión no grabada. Con los datos introducidos se superan las horas virtuales totales del curso.'
        );
    }
    public function testSesionNewCaso4(): void
    {
        $usuario = $this->createUserWithRole('ROLE_TEACHER');

        // Creamos una entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24504');
        $curso->setHoras(20);
        $curso->setHorasVirtuales(10);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos una entidad Edicion (Abierta)
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24504/01');
        $edicion->setEstado(0); // Abierta
        $edicion->setFechaInicio(new DateTime('+1 day'));
        $edicion->setFechaFin(new DateTime('+1 day'));
        $edicion->setSesiones(2);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);
        $this->manager->persist($edicion);

        // Crear Formador
        $formador = new Formador();
        $formador->setNif('12345678A');
        $formador->setNombre('John');
        $formador->setApellidos('Doe');
        $formador->setOrganizacion('USE');
        $formador->setUsuario($usuario);
        $this->manager->persist($formador);

        $usuario->setFormador($formador);
        $this->manager->persist($usuario);

        $this->manager->flush();

        // Loguear el usuario
        $this->client->loginUser($usuario);

        // ---- CASO 4: Superar el número máximo de sesiones ----
        // Crear una primera sesión válida
        $crawler = $this->client->request(
            'GET',
            '/intranet/forpas/formador/sesion-new/' . $edicion->getId()
        );

        $form = $crawler->selectButton('Guardar')->form([
            'sesion[fecha]' => (new DateTime('+1 day'))->format('Y-m-d'),
            'sesion[hora_inicio]' => '09:00',
            'sesion[duracionHoras]' => 10, // Horas
            'sesion[duracionMinutos]' => 0, // Minutos
            'sesion[tipo]' => 1, // Virtual
        ]);

        $this->client->submit($form);

        // Intentar crear una segunda sesión, que debería fallar
        $crawler = $this->client->request(
            'GET',
            '/intranet/forpas/formador/sesion-new/' . $edicion->getId()
        );

        $form = $crawler->selectButton('Guardar')->form([
            'sesion[fecha]' => (new DateTime('+1 day'))->format('Y-m-d'),
            'sesion[hora_inicio]' => '16:00',
            'sesion[duracionHoras]' => 10, // Horas
            'sesion[duracionMinutos]' => 0, // Minutos
            'sesion[tipo]' => 1, // Virtual
        ]);

        $this->client->submit($form);

        $this->client->followRedirect();
        $this->assertSelectorTextContains(
            '.alert-warning',
            'Sesión no grabada. Con los datos introducidos se superan las horas virtuales totales del curso.'
        );
    }
    public function testSesionEditCorrectamente(): void
    {
        $usuario = $this->createUserWithRole('ROLE_TEACHER');

        // Creamos una entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24501');
        $curso->setHoras(20);
        $curso->setHorasVirtuales(10);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos una entidad Edicion (Abierta)
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24501/01');
        $edicion->setEstado(0); // Abierta
        $edicion->setFechaInicio(new DateTime('+1 day'));
        $edicion->setFechaFin(new DateTime('+3 days'));
        $edicion->setSesiones(2);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);

        // Creamos una entidad Formador
        $formador = new Formador();
        $formador->setNif('12121212A');
        $formador->setNombre('John');
        $formador->setApellidos('Doe');
        $formador->setOrganizacion('Value3');
        $formador->setCorreoAux('aux@example.com');
        $formador->setTelefono('123456789');
        $formador->setObservaciones('Observaciones de prueba');
        $formador->setFormadorRJ(1);
        $formador->setUsuario($usuario);
        $this->manager->persist($formador);

        $usuario->setFormador($formador);
        $this->manager->persist($usuario);

        // Creamos sesiones
        $sesion = new Sesion();
        $sesion->setEdicion($edicion);
        $sesion->setFormador($formador);
        $sesion->setFecha(new DateTime('+1 day'));
        $sesion->setHoraInicio(new DateTime('09:00'));
        $sesion->setDuracion(600);
        $sesion->setTipo(0);
        $this->manager->persist($sesion);

        $edicion->addSesionesEdicion($sesion);
        $this->manager->persist($edicion);

        $this->manager->flush();
        $this->client->loginUser($usuario);

        // CASO 1: Editar sesión correctamente
        $crawler = $this->client->request('GET', '/intranet/forpas/formador/sesion-edit/' . $sesion->getId());
        $this->assertResponseIsSuccessful();

        // Modificar datos del formulario
        $form = $crawler->selectButton('Actualizar')->form([
            'sesion[fecha]' => (new \DateTime('+2 days'))->format('Y-m-d'),
            'sesion[hora_inicio]' => '10:00',
            'sesion[duracionHoras]' => 2, // Horas
            'sesion[duracionMinutos]' => 30, // Minutos
            'sesion[tipo]' => 1, // Virtual
        ]);

        $this->client->submit($form);

        // Verificar redirección
        $this->assertResponseRedirects(
            $this->getContainer()->get('router')->generate(
                'intranet_forpas_formador_mis_ediciones_show',
                ['id' => $edicion->getId()]
            )
        );

        // Confirmar cambios
        $this->manager->clear();
        $sesionActualizada = $this->manager->getRepository(Sesion::class)->find($sesion->getId());

        $this->assertEquals(
            (new DateTime('+2 days'))->format('Y-m-d'),
            $sesionActualizada->getFecha()->format('Y-m-d')
        );
        $this->assertEquals(
            '10:00',
            $sesionActualizada->getHoraInicio()->format('H:i')
        );
        $this->assertEquals(150, $sesionActualizada->getDuracion()); // 2 horas 30 minutos
        $this->assertEquals(1, $sesionActualizada->getTipo()); // Virtual
    }
    public function testSesionDelete(): void
    {
        $usuario = $this->createUserWithRole('ROLE_TEACHER');

        // Creamos una entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24501');
        $curso->setHoras(20);
        $curso->setHorasVirtuales(10);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos una entidad Edicion (Abierta)
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24501/01');
        $edicion->setEstado(0); // Abierta
        $edicion->setFechaInicio(new DateTime('+1 day'));
        $edicion->setFechaFin(new DateTime('+3 days'));
        $edicion->setSesiones(2);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);

        // Creamos una entidad Formador
        $formador = new Formador();
        $formador->setNif('12121212A');
        $formador->setNombre('John');
        $formador->setApellidos('Doe');
        $formador->setOrganizacion('Value3');
        $formador->setCorreoAux('aux@example.com');
        $formador->setTelefono('123456789');
        $formador->setObservaciones('Observaciones de prueba');
        $formador->setFormadorRJ(1);
        $formador->setUsuario($usuario);
        $this->manager->persist($formador);

        $usuario->setFormador($formador);
        $this->manager->persist($usuario);

        // Creamos sesiones
        $sesion = new Sesion();
        $sesion->setEdicion($edicion);
        $sesion->setFormador($formador);
        $sesion->setFecha(new DateTime('+1 day'));
        $sesion->setHoraInicio(new DateTime('09:00'));
        $sesion->setDuracion(600);
        $sesion->setTipo(0);
        $this->manager->persist($sesion);

        $edicion->addSesionesEdicion($sesion);
        $this->manager->persist($edicion);

        $this->manager->flush();
        $this->client->loginUser($usuario);

        // Acceder a la página que contiene el formulario de eliminación
        $crawler = $this->client->request('GET', '/intranet/forpas/formador/mis-ediciones/' . $sesion->getEdicion()->getId());

        $form = $crawler->filter('form button[type="submit"]')->form();
        $this->client->submit($form);

        // Verificar redirección
        $this->assertResponseRedirects('/intranet/forpas/formador/mis-ediciones/' . $sesion->getEdicion()->getId());

        // Verificar que la sesión fue eliminada
        $this->assertNull($this->manager->getRepository(Sesion::class)->find($sesion->getId()));
    }
    public function testFormPersistsNewAsistencias(): void
    {
        $usuario = $this->createUserWithRole('ROLE_TEACHER');
        $usuario2 = $this->createUserWithRole('ROLE_USER');

        // Creamos una entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24501');
        $curso->setHoras(20);
        $curso->setHorasVirtuales(10);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos una entidad Edicion (Abierta)
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24501/01');
        $edicion->setEstado(0); // Abierta
        $edicion->setFechaInicio(new DateTime('+1 day'));
        $edicion->setFechaFin(new DateTime('+3 days'));
        $edicion->setSesiones(2);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);

        // Creamos una entidad Formador
        $formador = new Formador();
        $formador->setNif('12121212A');
        $formador->setNombre('John');
        $formador->setApellidos('Doe');
        $formador->setOrganizacion('Value3');
        $formador->setCorreoAux('aux@example.com');
        $formador->setTelefono('123456789');
        $formador->setObservaciones('Observaciones de prueba');
        $formador->setFormadorRJ(1);
        $formador->setUsuario($usuario);
        $this->manager->persist($formador);

        $usuario->setFormador($formador);
        $this->manager->persist($usuario);

        // Creamos una entidad Participante
        $participante = new Participante();
        $participante->setNif('12345678A');
        $participante->setNombre('Johnny');
        $participante->setApellidos('Lopez');
        $participante->setUnidad('Unidad');
        $participante->setUsuario($usuario2);
        $this->manager->persist($participante);

        // Creamos una entidad ParticipanteEdicion
        $participanteEdicion = new ParticipanteEdicion();
        $participanteEdicion->setFechaSolicitud(new DateTime('2024-01-01'));
        $participanteEdicion->setParticipante($participante);
        $participanteEdicion->setEdicion($edicion);
        $this->manager->persist($participanteEdicion);

        // Creamos sesiones
        $sesion = new Sesion();
        $sesion->setEdicion($edicion);
        $sesion->setFormador($formador);
        $sesion->setFecha(new DateTime('+1 day'));
        $sesion->setHoraInicio(new DateTime('09:00'));
        $sesion->setDuracion(600);
        $sesion->setTipo(0);
        $this->manager->persist($sesion);

        $edicion->addSesionesEdicion($sesion);
        $edicion->addParticipantesEdicion($participanteEdicion);
        $this->manager->persist($edicion);

        $this->manager->flush();

        // Simulación de la petición GET
        $this->client->loginUser($usuario);
        $crawler = $this->client->request('GET', '/intranet/forpas/formador/sesion/'.$sesion->getId().'/asistencia');

        // Verificar que el formulario se renderiza
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        // Rellenar y enviar el formulario
        $form = $crawler->selectButton('Guardar')->form([
            'form[asistencias][0][estado]' => 'justifica',
            'form[asistencias][0][observaciones]' => 'Motivo justificado',
        ]);

        $this->client->submit($form);

        // Verificar redirección
        $this->assertResponseRedirects('/intranet/forpas/formador/mis-ediciones/'.$edicion->getId());

        // Verificar en la base de datos
        $asistenciaGuardada = $this->manager->getRepository(Asistencia::class)
            ->findOneBy(['sesion' => $sesion, 'participante' => $participante]);

        $this->assertNotNull($asistenciaGuardada);
        $this->assertTrue($asistenciaGuardada->isJustifica(), 'El estado "justifica" no se guardó correctamente.');
        $this->assertEquals('Motivo justificado', $asistenciaGuardada->getObservaciones());
    }
    public function testRegistrarCalificacionesSavesSuccessfully(): void
    {
        $usuario = $this->createUserWithRole('ROLE_TEACHER');
        $usuarioParticipante = $this->createUserWithRole('ROLE_USER');

        // Creamos una entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24501');
        $curso->setHoras(20);
        $curso->setHorasVirtuales(10);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos una entidad Edicion (Abierta)
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24501/01');
        $edicion->setEstado(0); // Abierta
        $edicion->setFechaInicio(new DateTime('+1 day'));
        $edicion->setFechaFin(new DateTime('+3 days'));
        $edicion->setSesiones(2);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);

        // Creamos una entidad Participante
        $participante = new Participante();
        $participante->setNif('12345678A');
        $participante->setNombre('Johnny');
        $participante->setApellidos('Lopez');
        $participante->setUnidad('Unidad');
        $participante->setUsuario($usuarioParticipante);
        $this->manager->persist($participante);

        $usuarioParticipante->setParticipante($participante);
        $this->manager->persist($usuarioParticipante);

        // Creamos una entidad ParticipanteEdicion
        $participanteEdicion = new ParticipanteEdicion();
        $participanteEdicion->setFechaSolicitud(new DateTime('2024-01-01'));
        $participanteEdicion->setParticipante($participante);
        $participanteEdicion->setEdicion($edicion);
        $this->manager->persist($participanteEdicion);

        $edicion->addParticipantesEdicion($participanteEdicion);
        $this->manager->persist($edicion);

        $this->manager->flush();
        $this->client->loginUser($usuario);

        // Realizamos la petición GET
        $crawler = $this->client->request('GET', '/intranet/forpas/formador/edicion/'.$edicion->getId().'/calificaciones');

        // Rellenamos y enviamos el formulario
        $form = $crawler->selectButton('Guardar')->form([
            'form[calificaciones_0][apto]' => 1, // Apto
            'form[calificaciones_0][pruebaFinal]' => 9.5, // Nota
        ]);

        $this->client->submit($form);

        // Verificamos la redirección
        $this->assertResponseRedirects('/intranet/forpas/formador/mis-ediciones/'.$edicion->getId());

        // Verificamos en la base de datos que los valores se han persistido
        $participanteEdicionActualizado = $this->manager->getRepository(ParticipanteEdicion::class)
            ->findOneBy(['edicion' => $edicion, 'participante' => $participante]);

        $this->assertNotNull($participanteEdicionActualizado);
        $this->assertEquals(1, $participanteEdicionActualizado->getApto(), 'El valor "apto" no se guardó correctamente.');
        $this->assertEquals(9.5, $participanteEdicionActualizado->getPruebaFinal(), 'La nota de prueba final no se guardó correctamente.');
    }
    public function testRegistrarCalificacionesAccessDeniedIfNotCalificable(): void
    {
        $usuario = $this->createUserWithRole('ROLE_TEACHER');

        // Creamos una edición NO calificable
        // Creamos una entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24501');
        $curso->setHoras(20);
        $curso->setHorasVirtuales(10);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setCalificable(false);
        $this->manager->persist($curso);

        // Creamos una entidad Edicion (Abierta)
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24501/01');
        $edicion->setEstado(0); // Abierta
        $edicion->setFechaInicio(new DateTime('+1 day'));
        $edicion->setFechaFin(new DateTime('+3 days'));
        $edicion->setSesiones(2);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);
        $this->manager->persist($edicion);

        $this->manager->flush();
        $this->client->loginUser($usuario);

        // Realizamos la petición
        $this->client->request('GET', '/intranet/forpas/formador/edicion/'.$edicion->getId().'/calificaciones');

        // Verificamos que se lanza una excepción de acceso denegado
        $this->assertResponseStatusCodeSame(302);
    }
}