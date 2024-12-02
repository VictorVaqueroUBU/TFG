<?php

namespace App\Tests\Controller\Forpas;

use App\Entity\Forpas\Curso;
use App\Entity\Forpas\Edicion;
use App\Entity\Forpas\Participante;
use App\Entity\Forpas\ParticipanteEdicion;
use App\Entity\Sistema\Usuario;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;

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
        $participante->setNif('22222222B');
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

        // Persistimos todo en la base de datos
        $this->manager->flush();

        // Hacemos una solicitud GET para cargar el formulario de edición
        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $participanteEdicion->getId()));

        // Verificamos que la respuesta es 200
        self::assertResponseStatusCodeSame(200);

        // Simulamos el envío del formulario con nuevos datos
        $this->client->submitForm('Actualizar', [
            'participante_edicion[observaciones]' => 'Nueva observación',
            'participante_edicion[prueba_final]' => '10.00',
            'participante_edicion[certificado]' => 'N',
        ]);

        // Verificamos que redirige correctamente
        self::assertResponseRedirects(sprintf('/intranet/forpas/gestor/participante_edicion/edicion/%s', $edicion->getId()));

        // Verificamos que los cambios se guardaron en la base de datos
        /** @var ParticipanteEdicion $actualizado */
        $actualizado = $this->repository->find($participanteEdicion->getId());
        self::assertSame('Nueva observación', $actualizado->getObservaciones());
        self::assertSame('10.00', $actualizado->getPruebaFinal());
        self::assertSame('N', $actualizado->getCertificado());
    }
    public function testRemoveSuccess(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');
        // Creamos la entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24005');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(0);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos la entidad Edicion
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24005/01');
        $edicion->setEstado(0);
        $edicion->setSesiones(3);
        $edicion->setMaxParticipantes(30);
        $edicion->setFechaInicio(new DateTime('+10 days'));
        $edicion->setCurso($curso);
        $curso->addEdiciones($edicion);
        $this->manager->persist($edicion);

        // Creamos la entidad Participante
        $participante = new Participante();
        $participante->setNif('55555555E');
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

        // Generamos el token CSRF
        $csrfTokenManager = static::getContainer()->get('security.csrf.token_manager');
        $token = $csrfTokenManager->getToken('delete' . $participanteEdicion->getId());

        // Realizamos la solicitud POST
        $this->client->request('POST', sprintf('%s%s', $this->path, $participanteEdicion->getId()), [
            '_token' => $token->getValue(),
        ]);

        // Verificamos la redirección
        self::assertResponseRedirects(sprintf('/intranet/forpas/gestor/participante_edicion/edicion/%s', $edicion->getId()));
    }
}