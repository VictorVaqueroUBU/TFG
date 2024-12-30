<?php

namespace App\Tests\Controller\Forpas;

use App\Entity\Forpas\Asistencia;
use App\Entity\Forpas\Curso;
use App\Entity\Forpas\Edicion;
use App\Entity\Forpas\Formador;
use App\Entity\Forpas\FormadorEdicion;
use App\Entity\Forpas\Participante;
use App\Entity\Forpas\ParticipanteEdicion;
use App\Entity\Forpas\Sesion;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

final class ParticipanteEdicionControllerTest extends BaseControllerTest
{
    /**
     * @var EntityRepository<ParticipanteEdicion>
     */
    private EntityRepository $repository;
    private string $path = '/intranet/forpas/gestor/participante_edicion/';
    protected function setUp(): void
    {
        parent::setUp(); // Llama al setUp de la clase base

        // Creamos una sesión activa
        $session = static::getContainer()->get('session.factory')->createSession();
        $session->start();

        // Creamos una solicitud simulada con la sesión activa
        $request = new Request();
        $request->setSession($session);
        static::getContainer()->get('request_stack')->push($request);

        // Añadimos la cookie de sesión al cliente
        $cookieJar = $this->client->getCookieJar();
        $cookieJar->set(new Cookie($session->getName(), $session->getId()));

        $this->manager = static::getContainer()->get('doctrine')->getManager();
        // Limpieza completa de la base de datos
        $schemaTool = new SchemaTool($this->manager);
        $classes = $this->manager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);

        $this->manager->flush();

        // Creamos y autenticamos un usuario por defecto
        $this->client->loginUser($this->createUserWithRole('ROLE_ADMIN'));
        // Asignamos el repositorio de ParticipanteEdicion para los tests
        $this->repository = $this->manager->getRepository(ParticipanteEdicion::class);
    }
    public function testIndex(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');
        // Creamos una entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24001');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(0);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos una entidad Edicion asociada al Curso
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24001/01');
        $edicion->setEstado(0);
        $edicion->setSesiones(2);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);
        $this->manager->persist($edicion);

        // Creamos una entidad Participante
        $participante = new Participante();
        $participante->setNif('11111111A');
        $participante->setNombre('John');
        $participante->setApellidos('Doe');
        $participante->setUsuario($usuario);
        $this->manager->persist($participante);

        // Creamos una entidad ParticipanteEdicion asociada a la Edicion y al Participante
        $participanteEdicion = new ParticipanteEdicion();
        $participanteEdicion->setEdicion($edicion); // Asociar Edicion
        $participanteEdicion->setParticipante($participante); // Asociar Participante
        $participanteEdicion->setFechaSolicitud(new DateTime('2024-01-01'));
        $this->manager->persist($participanteEdicion);

        // Persistimos todos los datos en la base de datos
        $this->manager->flush();

        // Obtenemos el ID generado automáticamente para la Edicion
        $edicionId = $edicion->getId();

        // Realizamos la solicitud al controlador
        $this->client->request('GET', "/intranet/forpas/gestor/participante_edicion/edicion/$edicionId");

        // Verificamos el código de respuesta HTTP
        self::assertResponseStatusCodeSame(200);

        // Verificamos que la tabla contiene los datos esperados en columnas específicas
        self::assertSelectorTextContains('#datosParticipantesEdicion tbody tr:first-child td:nth-child(1)', '11111111A'); // NIF
        self::assertSelectorTextContains('#datosParticipantesEdicion tbody tr:first-child td:nth-child(2)', 'Doe');       // Apellidos
        self::assertSelectorTextContains('#datosParticipantesEdicion tbody tr:first-child td:nth-child(3)', 'John');      // Nombre
    }
    public function testCertificar(): void
    {
        // Creamos el usuario y autenticamos
        $usuarioP1 = $this->createUserWithRole('ROLE_USER');
        $usuarioP2 = $this->createUserWithRole('ROLE_USER');

        // Creamos el Curso
        $curso = new Curso();
        $curso->setCodigoCurso('30010');
        $curso->setNombreCurso('Curso de Certificación');
        $curso->setHoras(10);
        $curso->setParticipantesEdicion(2);
        $curso->setEdicionesEstimadas(1);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(0);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos la Edición
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('30010/01');
        $edicion->setEstado(0);
        $edicion->setSesiones(2);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);
        $this->manager->persist($edicion);

        // Creamos la entidad Formador
        $formador = new Formador();
        $formador->setNif('12345678Z');
        $formador->setNombre('Formador Ejemplo');
        $formador->setApellidos('Apellido');
        $formador->setOrganizacion('Organización');
        $formador->setUsuario($this->createUserWithRole('ROLE_TEACHER'));
        $this->manager->persist($formador);

        // Creamos una entidad FormadorEdicion
        $formadorEdicion = new FormadorEdicion();
        $formadorEdicion->setFormador($formador);
        $formadorEdicion->setEdicion($edicion);
        $this->manager->persist($formadorEdicion);

        // Creamos Participantes y ParticipanteEdicion
        $participante1 = new Participante();
        $participante1->setNif('11111111B');
        $participante1->setNombre('Carlos');
        $participante1->setApellidos('García');
        $participante1->setOrganizacion('Org1');
        $participante1->setUnidad('Unidad A');
        $participante1->setUsuario($usuarioP1);
        $this->manager->persist($participante1);

        $participanteEdicion1 = new ParticipanteEdicion();
        $participanteEdicion1->setParticipante($participante1);
        $participanteEdicion1->setEdicion($edicion);
        $participanteEdicion1->setApto(1);
        $participanteEdicion1->setPruebaFinal('8.5');
        $participanteEdicion1->setFechaSolicitud(new DateTime('2024-01-01'));
        $this->manager->persist($participanteEdicion1);

        $participante2 = new Participante();
        $participante2->setNif('22222222B');
        $participante2->setNombre('María');
        $participante2->setApellidos('López');
        $participante2->setOrganizacion('Org2');
        $participante2->setUnidad('Unidad B');
        $participante2->setUsuario($usuarioP2);
        $this->manager->persist($participante2);

        $participanteEdicion2 = new ParticipanteEdicion();
        $participanteEdicion2->setParticipante($participante2);
        $participanteEdicion2->setEdicion($edicion);
        $participanteEdicion2->setApto(0); // no apto
        $participanteEdicion2->setPruebaFinal('N/A');
        $participanteEdicion2->setFechaSolicitud(new DateTime('2024-01-01'));
        $participanteEdicion2->setBajaJustificada(new DateTime('2024-01-02'));
        $this->manager->persist($participanteEdicion2);

        // Creamos Sesiones de la Edición
        $sesion1 = new Sesion();
        $sesion1->setEdicion($edicion);
        $sesion1->setFecha(new DateTime('2024-01-01'));
        $sesion1->setHoraInicio(new DateTime('2024-01-01 09:00:00'));
        $sesion1->setDuracion(60);
        $sesion1->setTipo(0);
        $sesion1->setFormador($formador);
        $this->manager->persist($sesion1);

        $sesion2 = new Sesion();
        $sesion2->setEdicion($edicion);
        $sesion2->setFecha(new DateTime('2024-01-02'));
        $sesion2->setHoraInicio(new DateTime('2024-01-01 09:00:00'));
        $sesion2->setDuracion(120);
        $sesion2->setTipo(0);
        $sesion2->setFormador($formador);
        $this->manager->persist($sesion2);

        $this->manager->flush();

        // Creamos Asistencias
        // Participante1 asiste el primer día, justifica el segundo
        $asistencia1_p1 = new Asistencia();
        $asistencia1_p1->setParticipante($participante1);
        $asistencia1_p1->setSesion($sesion1);
        $asistencia1_p1->setEstado('asiste'); // día 1 asiste
        $asistencia1_p1->setFormador($formador);
        $this->manager->persist($asistencia1_p1);

        $asistencia2_p1 = new Asistencia();
        $asistencia2_p1->setParticipante($participante1);
        $asistencia2_p1->setSesion($sesion2);
        $asistencia2_p1->setEstado('justifica'); // día 2 justifica
        $asistencia2_p1->setFormador($formador);
        $this->manager->persist($asistencia2_p1);

        // Participante2 no asiste el primer día, no asiste ni justifica el segundo (ninguno)
        $asistencia1_p2 = new Asistencia();
        $asistencia1_p2->setParticipante($participante2);
        $asistencia1_p2->setSesion($sesion1);
        $asistencia1_p2->setEstado('ninguno');
        $asistencia1_p2->setFormador($formador);
        $this->manager->persist($asistencia1_p2);

        $asistencia2_p2 = new Asistencia();
        $asistencia2_p2->setParticipante($participante2);
        $asistencia2_p2->setSesion($sesion2);
        $asistencia2_p2->setEstado('ninguno');
        $asistencia2_p2->setFormador($formador);
        $this->manager->persist($asistencia2_p2);

        $this->manager->flush();

        // Realizamos la petición GET al método certificar
        $this->client->request('GET', sprintf('/intranet/forpas/gestor/participante_edicion/edicion/%d/certificar', $edicion->getId()));

        // Verificamos que la respuesta es correcta
        self::assertResponseStatusCodeSame(200);

        $responseContent = $this->client->getResponse()->getContent();

        // Comprobamos que aparecen datos de los participantes
        self::assertStringContainsString('Carlos García', $responseContent);
        self::assertStringContainsString('María López', $responseContent);

        // Comprobamos que se reflejan las asistencias (Carlos asistió un día, así que días=1, minutos=60)
        self::assertStringContainsString('>1<', $responseContent);    // para verificar que aparece "1" en la celda de días
        self::assertStringContainsString('1h', $responseContent);     // para verificar las horas en formato "1h"

        // En cambio, María no asistió ningún día, ni justificó, así que no debería mostrar días de asistencia
        // Dependiendo de cómo se muestre en la vista, comprueba la lógica correspondiente
        self::assertStringNotContainsString('1 día', $responseContent, 'María no debe tener un día de asistencia');
        // Podrías comprobar otra lógica presente en la vista si se muestra por filas o columnas específicas.
    }
    public function testCalcularCertificados(): void
    {
        $usuario = $this->createUserWithRole('ROLE_ADMIN');
        $this->client->loginUser($usuario);

        // Creamos el Curso (calificable y con horas)
        $curso = new Curso();
        $curso->setCodigoCurso('24010'); // Por ejemplo: "24010" => año curso = '2024'
        $curso->setNombreCurso('Curso de Certificación Test');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(2);
        $curso->setEdicionesEstimadas(1);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(0);
        $curso->setCalificable(true); // Importante: es calificable
        $this->manager->persist($curso);

        // Creamos la Edición
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24010/01');
        $edicion->setEstado(0);
        $edicion->setSesiones(2);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);
        $this->manager->persist($edicion);

        // Creamos un participante que cumple asistencia y apto
        $usuarioP1 = $this->createUserWithRole('ROLE_USER');
        $participante1 = new Participante();
        $participante1->setNif('11111111A');
        $participante1->setNombre('Nombre1');
        $participante1->setApellidos('Apellido1');
        $participante1->setUsuario($usuarioP1);
        $participante1->setOrganizacion('Org');
        $participante1->setUnidad('Unidad');
        $this->manager->persist($participante1);

        $participanteEdicion1 = new ParticipanteEdicion();
        $participanteEdicion1->setParticipante($participante1);
        $participanteEdicion1->setEdicion($edicion);
        $participanteEdicion1->setFechaSolicitud(new DateTime('2024-01-01'));
        // apto=1 y asistencia suficiente => debe recibir certificado 'S'
        // Por ejemplo, 20 horas curso => 75% => 15 horas * 60 = 900 minutos mínimos
        // Asignamos datos en el JSON (no en la entidad) para simular el POST
        $this->manager->persist($participanteEdicion1);

        // Creamos otro participante que no cumple, por ejemplo apto=0 o menos asistencia
        $usuarioP2 = $this->createUserWithRole('ROLE_USER');
        $participante2 = new Participante();
        $participante2->setNif('22222222B');
        $participante2->setNombre('Nombre2');
        $participante2->setApellidos('Apellido2');
        $participante2->setUsuario($usuarioP2);
        $participante2->setOrganizacion('Org');
        $participante2->setUnidad('Unidad');
        $this->manager->persist($participante2);

        $participanteEdicion2 = new ParticipanteEdicion();
        $participanteEdicion2->setParticipante($participante2);
        $participanteEdicion2->setEdicion($edicion);
        $participanteEdicion2->setFechaSolicitud(new DateTime('2024-01-01'));
        // apto=0 o insuficiente asistencia => debe quedar certificado='N'
        $this->manager->persist($participanteEdicion2);

        // Persistimos antes de enviar el POST
        $this->manager->flush();

        // Preparamos los datos que enviaría el formulario
        // Caso participante1:
        // apto=1, pruebaFinal='9.0', bajaJustificada=null, dias=2, minutosAsistencia=1200 (20h * 60 = 1200 => cumple 100% > 75%)
        $datosParticipantes = [
            $participante1->getId() => [
                'nif' => $participante1->getNif(),
                'apellidos' => $participante1->getApellidos(),
                'nombre' => $participante1->getNombre(),
                'apto' => 1,
                'pruebaFinal' => '9.0',
                'bajaJustificada' => null,
                'certificado' => null, // actualmente no certificado
                'libro' => null,
                'numeroTitulo' => null,
                'dias' => 2,
                'minutosAsistencia' => 1200,
                'asistenciasFechas' => [],
                'justificacionesFechas' => [],
            ],
            $participante2->getId() => [
                'nif' => $participante2->getNif(),
                'apellidos' => $participante2->getApellidos(),
                'nombre' => $participante2->getNombre(),
                'apto' => 0, // no apto
                'pruebaFinal' => 'N/A',
                'bajaJustificada' => null,
                'certificado' => null,
                'libro' => null,
                'numeroTitulo' => null,
                'dias' => 0,
                'minutosAsistencia' => 0,
                'asistenciasFechas' => [],
                'justificacionesFechas' => [],
            ],
        ];

        // Generamos el token CSRF si el formulario lo requiere
        $csrfTokenManager = static::getContainer()->get('security.csrf.token_manager');
        $token = $csrfTokenManager->getToken('some_token_id'); // Ajusta si tu formulario requiere un token con ID específico

        // Enviamos la petición POST
        $this->client->request('POST', sprintf('/intranet/forpas/gestor/participante_edicion/edicion/%d/certificar/procesar', $edicion->getId()), [
            '_token' => $token->getValue(),
            'datos_participantes' => json_encode($datosParticipantes),
        ]);

        // Verificamos la redirección tras el procesamiento
        self::assertResponseRedirects(sprintf('/intranet/forpas/gestor/participante_edicion/edicion/%d/certificar', $edicion->getId()));
        $this->client->followRedirect();

        // Verificamos el mensaje flash
        $responseContent = $this->client->getResponse()->getContent();
        self::assertStringContainsString('Edición certificada correctamente.', $responseContent);

        // Verificamos en BD que la edición se ha actualizado a estado '2'
        $this->manager->clear();
        $edicionActualizada = $this->manager->getRepository(Edicion::class)->find($edicion->getId());
        self::assertSame('2', (string)$edicionActualizada->getEstado(), 'La edición debería estar en estado "2" tras certificar.');

        // Verificamos el estado de los ParticipanteEdicion
        $partEd1Actualizado = $this->manager->getRepository(ParticipanteEdicion::class)->find($participanteEdicion1->getId());
        // Debe tener certificado 'S', libro=2024 y numeroTitulo=ultimoTitulo+1 => como no había títulos antes, numeroTitulo=1
        self::assertSame('S', $partEd1Actualizado->getCertificado());
        self::assertSame(2024, $partEd1Actualizado->getLibro());
        self::assertSame(1, $partEd1Actualizado->getNumeroTitulo());

        $partEd2Actualizado = $this->manager->getRepository(ParticipanteEdicion::class)->find($participanteEdicion2->getId());
        // No cumple, debe tener certificado = 'N'
        self::assertSame('N', $partEd2Actualizado->getCertificado());
    }
    public function testCalcularCertificadosEdicionNoEncontrada(): void
    {
        $usuario = $this->createUserWithRole('ROLE_ADMIN');
        $this->client->loginUser($usuario);

        // No creamos ninguna Edición, así que el ID que usaremos no existirá en la BD.
        $edicionIdInexistente = 999999;

        // Simulamos el envío del POST con datos vacíos o mínimos
        $this->client->request(
            'POST',
            sprintf('/intranet/forpas/gestor/participante_edicion/edicion/%d/certificar/procesar', $edicionIdInexistente),
            ['datos_participantes' => json_encode([])]
        );

        // Verificamos que la respuesta es una redirección a /intranet
        self::assertResponseRedirects('/intranet');

        // Recuperar mensajes flash desde el cliente simulado
        $session = $this->client->getRequest()->getSession();
        if ($session instanceof Session) {
            $flashes = $session->getFlashBag()->get('warning');
        } else {
            throw new \RuntimeException('La sesión no es válida. FlashBag no disponible.');
        }

        // Verificar que el mensaje flash contiene el texto esperado
        self::assertContains('Ruta no encontrada', $flashes);
    }
    public function testNew(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');
        // Creamos una entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24002');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(0);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos una entidad Edicion asociada al Curso
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24002/01');
        $edicion->setEstado(0);
        $edicion->setSesiones(3);
        $edicion->setMaxParticipantes(30);
        $edicion->setCurso($curso);
        $curso->addEdiciones($edicion);
        $this->manager->persist($edicion);

        // Creamos una entidad Participante
        $participante = new Participante();
        $participante->setNif('22222222C');
        $participante->setNombre('Jane');
        $participante->setApellidos('Smith');
        $participante->setUsuario($usuario);
        $this->manager->persist($participante);

        // Persistimos las entidades previas
        $this->manager->flush();

        // Validamos persistencia
        $participantePersistido = $this->manager->find(Participante::class, $participante->getId());
        $edicionPersistida = $this->manager->find(Edicion::class, $edicion->getId());

        self::assertNotNull($participantePersistido, 'Participante no encontrado en la base de datos.');
        self::assertNotNull($edicionPersistida, 'Edición no encontrada en la base de datos.');

        // Simulamos la llamada al método new con los ID de Participante y Edición
        $this->client->request('GET', "/intranet/forpas/gestor/participante_edicion/new/{$participante->getId()}/{$edicion->getId()}");

        // Validamos que redirige correctamente
        self::assertResponseRedirects("/intranet/forpas/gestor/participante_edicion/edicion/{$edicion->getId()}");

        // Verificamos que la inscripción se ha creado en la base de datos
        $participanteEdiciones = $this->repository->findAll();
        self::assertCount(1, $participanteEdiciones);

        /** @var ParticipanteEdicion $nuevaInscripcion */
        $nuevaInscripcion = $participanteEdiciones[0];
        self::assertSame($participante->getId(), $nuevaInscripcion->getParticipante()->getId());
        self::assertSame($edicion->getId(), $nuevaInscripcion->getEdicion()->getId());
        self::assertNotNull($nuevaInscripcion->getFechaSolicitud());
    }
    public function testShow(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');
        // Creamos una entidad Curso
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

        // Creamos una entidad Edicion asociada al Curso
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24003/01');
        $edicion->setEstado(0);
        $edicion->setSesiones(3);
        $edicion->setMaxParticipantes(30);
        $edicion->setCurso($curso);
        $curso->addEdiciones($edicion);
        $this->manager->persist($edicion);

        // Creamos una entidad Participante
        $participante = new Participante();
        $participante->setNif('33333333C');
        $participante->setNombre('Jane');
        $participante->setApellidos('Doe');
        $participante->setUsuario($usuario);
        $this->manager->persist($participante);

        // Creamos una entidad ParticipanteEdicion
        $participanteEdicion = new ParticipanteEdicion();
        $participanteEdicion->setFechaSolicitud(new DateTime('2024-01-01'));
        $participanteEdicion->setBajaJustificada(null);
        $participanteEdicion->setPruebaFinal('5');
        $participanteEdicion->setCertificado('S');
        $participanteEdicion->setLibro(2024);
        $participanteEdicion->setNumeroTitulo(1);
        $participanteEdicion->setObservaciones('Observación de prueba');
        $participanteEdicion->setApto(1);
        $participanteEdicion->setDireccion('Calle Falsa 123');
        $participanteEdicion->setParticipante($participante);
        $participanteEdicion->setEdicion($edicion);
        $this->manager->persist($participanteEdicion);

        // Persistimos en la base de datos
        $this->manager->flush();

        // Hacemos una solicitud GET al método show
        $this->client->request('GET', sprintf('%s%s', $this->path, $participanteEdicion->getId()));

        // Verificamos que la respuesta es 200
        self::assertResponseStatusCodeSame(200);

        // Verificamos que los datos se muestran correctamente en los campos
        self::assertSelectorTextContains('div:contains("Nif") + .fila-valor', '33333333C'); // NIF
        self::assertSelectorTextContains('div:contains("Apellidos") + .fila-valor', 'Doe'); // Apellidos
        self::assertSelectorTextContains('div:contains("Nombre") + .fila-valor', 'Jane'); // Nombre
    }
    public function testEdit(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');
        // Creamos una entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24004');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(0);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos una entidad Edicion asociada al Curso
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24004/01');
        $edicion->setEstado(0);
        $edicion->setSesiones(3);
        $edicion->setMaxParticipantes(30);
        $edicion->setCurso($curso);
        $curso->addEdiciones($edicion);
        $this->manager->persist($edicion);

        // Creamos una entidad Participante
        $participante = new Participante();
        $participante->setNif('44444444D');
        $participante->setNombre('Jane');
        $participante->setApellidos('Doe');
        $participante->setUsuario($usuario);
        $this->manager->persist($participante);

        // Creamos una entidad ParticipanteEdicion
        $participanteEdicion = new ParticipanteEdicion();
        $participanteEdicion->setFechaSolicitud(new DateTime('2024-01-01'));
        $participanteEdicion->setParticipante($participante);
        $participanteEdicion->setEdicion($edicion);
        $this->manager->persist($participanteEdicion);

        // Persistimos en la base de datos
        $this->manager->flush();

        // Hacemos una solicitud GET para cargar el formulario de edición
        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $participanteEdicion->getId()));

        // Verificamos que la respuesta es 200
        self::assertResponseStatusCodeSame(200);

        // Simulamos el envío del formulario con nuevos datos
        $this->client->submitForm('Actualizar', [
            'participante_edicion[observaciones]' => 'Nueva observación',
            'participante_edicion[baja_justificada]' => '2024-12-19 10:30:00',
        ]);
        // Verificamos que redirige correctamente
        self::assertResponseRedirects(sprintf('/intranet/forpas/gestor/participante_edicion/edicion/%s', $edicion->getId()));

        // Verificamos que los cambios se guardaron en la base de datos
        /** @var ParticipanteEdicion $actualizado */
        $actualizado = $this->repository->find($participanteEdicion->getId());
        self::assertSame('Nueva observación', $actualizado->getObservaciones());
        self::assertSame('2024-12-19 10:30:00', $actualizado->getBajaJustificada()->format('Y-m-d H:i:s'));
    }
    public function testDeleteEdicionFechaPasada(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');
        // Crear un curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba2');
        $curso->setCodigoCurso('24006');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(0);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Crear una edición con fecha anterior a hoy
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24006/01');
        $edicion->setEstado(0);
        $edicion->setSesiones(3);
        $edicion->setMaxParticipantes(30);
        $edicion->setFechaInicio((new DateTime())->modify('-1 day'));
        $edicion->setCurso($curso);
        $curso->addEdiciones($edicion);
        $this->manager->persist($edicion);

        // Creamos la entidad Participante
        $participante = new Participante();
        $participante->setNif('55555555F');
        $participante->setNombre('Víctor');
        $participante->setApellidos('Vaquero');
        $participante->setUsuario($usuario);
        $this->manager->persist($participante);

        // Creamos la entidad ParticipanteEdicion
        $participanteEdicion = new ParticipanteEdicion();
        $participanteEdicion->setFechaSolicitud(new DateTime('2024-01-01'));
        $participanteEdicion->setParticipante($participante);
        $participanteEdicion->setEdicion($edicion);
        $participante->addParticipanteEdiciones($participanteEdicion);
        $edicion->addParticipantesEdicion($participanteEdicion);
        $this->manager->persist($participanteEdicion);

        $this->manager->flush();

        // Ejecutar la petición DELETE
        $this->client->request('POST', '/intranet/forpas/gestor/participante_edicion/' . $participanteEdicion->getId(), [
            '_token' => $this->client->getContainer()->get('security.csrf.token_manager')->getToken('delete' . $participanteEdicion->getId())
        ]);

        // Verificar que se muestra un mensaje de advertencia
        $this->assertResponseRedirects('/intranet/forpas/gestor/participante_edicion/edicion/1');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-warning', 'No se puede eliminar a un participante de una edición que ya ha comenzado.');
    }
    public function testParticipanteEdicionDelete(): void
    {
        $usuario = $this->createUserWithRole('ROLE_ADMIN');

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

        // Creamos la entidad Participante
        $participante = new Participante();
        $participante->setNif('55555555F');
        $participante->setNombre('Víctor');
        $participante->setApellidos('Vaquero');
        $participante->setUsuario($usuario);
        $this->manager->persist($participante);

        $usuario->setParticipante($participante);
        $this->manager->persist($usuario);

        // Creamos la entidad ParticipanteEdicion
        $participanteEdicion = new ParticipanteEdicion();
        $participanteEdicion->setFechaSolicitud(new DateTime('2024-01-01'));
        $participanteEdicion->setParticipante($participante);
        $participanteEdicion->setEdicion($edicion);
        $participante->addParticipanteEdiciones($participanteEdicion);
        $edicion->addParticipantesEdicion($participanteEdicion);
        $this->manager->persist($participanteEdicion);

        $this->manager->flush();

        // Loguear el usuario
        $this->client->loginUser($usuario);

        // Acceder y enviar el formulario de eliminación
        $crawler = $this->client->request('GET', '/intranet/forpas/gestor/participante_edicion/edicion/' . $edicion->getId());
        $form = $crawler->filter('form button[title="Eliminar"]')->form();
        $this->client->submit($form);

        // Verificar la redirección y la eliminación
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->assertNull(
            $this->manager->getRepository(ParticipanteEdicion::class)->find($participanteEdicion->getId()),
            'La relación Participante-Edicion no fue eliminada correctamente.'
        );
    }

}