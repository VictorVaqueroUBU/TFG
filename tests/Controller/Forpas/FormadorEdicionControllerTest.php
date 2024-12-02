<?php

namespace App\Tests\Controller\Forpas;

use App\Entity\Forpas\Curso;
use App\Entity\Forpas\Edicion;
use App\Entity\Forpas\Formador;
use App\Entity\Forpas\FormadorEdicion;
use App\Entity\Sistema\Usuario;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;

final class FormadorEdicionControllerTest extends BaseControllerTest
{
    /**
     * @var EntityRepository<FormadorEdicion>
     */
    private EntityRepository $repository;
    private string $path = '/intranet/forpas/gestor/formador_edicion/';
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

        // Limpiamos datos de FormadorEdicion, Edicion, Curso y Formador
        $repositories = [
            FormadorEdicion::class,
            Formador::class,
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
        // Asignamos el repositorio de FormadorEdicion para los tests
        $this->repository = $this->manager->getRepository(FormadorEdicion::class);
    }
    public function testIndex(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');
        // Creamos una entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24301');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(0);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos una entidad Edicion asociada al Curso
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24301/01');
        $edicion->setEstado(0);
        $edicion->setSesiones(2);
        $edicion->setMaxParticipantes(20);
        $edicion->setCurso($curso);
        $this->manager->persist($edicion);

        // Creamos una entidad Formador
        $formador = new Formador();
        $formador->setNif('12121212A');
        $formador->setNombre('John');
        $formador->setApellidos('Doe');
        $formador->setOrganizacion('Value3');
        $formador->setCorreoAux('Value3');
        $formador->setTelefono('Value3');
        $formador->setObservaciones('Value3');
        $formador->setFormadorRJ(1);
        $formador->setUsuario($usuario);
        $this->manager->persist($formador);

        // Creamos una entidad FormadorEdicion asociada a la Edicion y al Formador
        $formadorEdicion = new FormadorEdicion();
        $formadorEdicion->setEdicion($edicion); // Asociar Edicion
        $formadorEdicion->setFormador($formador); // Asociar Participante
        $this->manager->persist($formadorEdicion);

        // Persistimos todos los datos en la base de datos
        $this->manager->flush();

        // Obtenemos el ID generado automáticamente para la Edicion
        $edicionId = $edicion->getId();

        // Realizamos la solicitud al controlador
        $this->client->request('GET', "/intranet/forpas/gestor/formador_edicion/edicion/$edicionId");

        // Verificamos el código de respuesta HTTP
        self::assertResponseStatusCodeSame(200);

        // Verificamos que la tabla contiene los datos esperados en columnas específicas
        self::assertSelectorTextContains('#datosFormadorEdicion tbody tr:first-child td:nth-child(1)', 'John Doe');
    }
    public function testNew(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');
        // Creamos una entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24302');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(0);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos una entidad Edicion asociada al Curso
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24302/01');
        $edicion->setEstado(0);
        $edicion->setSesiones(3);
        $edicion->setMaxParticipantes(30);
        $edicion->setCurso($curso);
        $curso->addEdiciones($edicion);
        $this->manager->persist($edicion);

        // Creamos una entidad Formador
        $formador = new Formador();
        $formador->setNif('21212121B');
        $formador->setNombre('Jane');
        $formador->setApellidos('Smith');
        $formador->setOrganizacion('USE');
        $formador->setUsuario($usuario);
        $this->manager->persist($formador);

        // Persistimos las entidades previas
        $this->manager->flush();

        // Validamos persistencia
        $formadorPersistido = $this->manager->find(Formador::class, $formador->getId());
        $edicionPersistida = $this->manager->find(Edicion::class, $edicion->getId());

        self::assertNotNull($formadorPersistido, 'Formador no encontrado en la base de datos.');
        self::assertNotNull($edicionPersistida, 'Edición no encontrada en la base de datos.');

        // Simulamos la llamada al método new con los ID de Formador y Edición
        $this->client->request('GET', "/intranet/forpas/gestor/formador_edicion/new/{$formador->getId()}/{$edicion->getId()}");

        // Validamos que redirige correctamente
        self::assertResponseRedirects("/intranet/forpas/gestor/formador_edicion/edicion/{$edicion->getId()}");

        // Verificamos que la inscripción se ha creado en la base de datos
        $formadorEdiciones = $this->repository->findAll();
        self::assertCount(1, $formadorEdiciones);

        /** @var FormadorEdicion $nuevaInscripcion */
        $nuevaInscripcion = $formadorEdiciones[0];
        self::assertSame($formador->getId(), $nuevaInscripcion->getFormador()->getId());
        self::assertSame($edicion->getId(), $nuevaInscripcion->getEdicion()->getId());
    }
    public function testShow(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');
        // Creamos una entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24303');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(0);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos una entidad Edicion asociada al Curso
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24303/01');
        $edicion->setEstado(0);
        $edicion->setSesiones(3);
        $edicion->setMaxParticipantes(30);
        $edicion->setCurso($curso);
        $curso->addEdiciones($edicion);
        $this->manager->persist($edicion);

        // Creamos una entidad Formador
        $formador = new Formador();
        $formador->setNif('31313131C');
        $formador->setNombre('Jane');
        $formador->setApellidos('Doe');
        $formador->setOrganizacion('USE');
        $formador->setUsuario($usuario);
        $this->manager->persist($formador);

        // Creamos una entidad FormadorEdicion
        $formadorEdicion = new FormadorEdicion();
        $formadorEdicion->setFormador($formador);
        $formadorEdicion->setEdicion($edicion);
        $this->manager->persist($formadorEdicion);

        $this->manager->flush();

        // Hacemos una solicitud GET al método show
        $this->client->request('GET', sprintf('%s%s', $this->path, $formadorEdicion->getId()));

        // Verificamos que la respuesta es 200
        self::assertResponseStatusCodeSame(200);

        // Verificamos que los datos se muestran correctamente en los campos
        self::assertSelectorTextContains('div:contains("Nif") + .fila-valor', '31313131C'); // NIF
        self::assertSelectorTextContains('div:contains("Apellidos") + .fila-valor', 'Doe'); // Apellidos
        self::assertSelectorTextContains('div:contains("Nombre") + .fila-valor', 'Jane'); // Nombre
    }
    public function testEdit(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');
        // Creamos una entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24304');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(0);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos una entidad Edicion asociada al Curso
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24304/01');
        $edicion->setEstado(0);
        $edicion->setSesiones(3);
        $edicion->setMaxParticipantes(30);
        $edicion->setCurso($curso);
        $curso->addEdiciones($edicion);
        $this->manager->persist($edicion);

        // Creamos una entidad Participante
        $formador = new Formador();
        $formador->setNif('42424242D');
        $formador->setNombre('Jane');
        $formador->setApellidos('Doe');
        $formador->setOrganizacion('USE');
        $formador->setUsuario($usuario);
        $this->manager->persist($formador);

        // Creamos una entidad FormadorEdicion
        $formadorEdicion = new FormadorEdicion();
        $formadorEdicion->setFormador($formador);
        $formadorEdicion->setEdicion($edicion);
        $this->manager->persist($formadorEdicion);

        $this->manager->flush();

        // Hacemos una solicitud GET para cargar el formulario de edición
        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $formadorEdicion->getId()));

        // Verificamos que la respuesta es 200
        self::assertResponseStatusCodeSame(200);

        // Simulamos el envío del formulario con nuevos datos
        $this->client->submitForm('Actualizar', [
            'formador_edicion[observaciones]' => 'Nueva observación',
        ]);

        // Verificamos que redirige correctamente
        self::assertResponseRedirects(sprintf('/intranet/forpas/gestor/formador_edicion/edicion/%s', $edicion->getId()));

        // Verificamos que los cambios se guardaron en la base de datos
        /** @var FormadorEdicion $actualizado */
        $actualizado = $this->repository->find($formadorEdicion->getId());
        self::assertSame('Nueva observación', $actualizado->getObservaciones());
    }
    public function testRemoveSuccess(): void
    {
        $usuario = $this->createUserWithRole('ROLE_USER');
        // Creamos la entidad Curso
        $curso = new Curso();
        $curso->setNombreCurso('Curso de Prueba');
        $curso->setCodigoCurso('24305');
        $curso->setHoras(20);
        $curso->setParticipantesEdicion(20);
        $curso->setEdicionesEstimadas(2);
        $curso->setVisibleWeb(true);
        $curso->setHorasVirtuales(0);
        $curso->setCalificable(true);
        $this->manager->persist($curso);

        // Creamos la entidad Edicion
        $edicion = new Edicion();
        $edicion->setCodigoEdicion('24305/01');
        $edicion->setEstado(0);
        $edicion->setSesiones(3);
        $edicion->setMaxParticipantes(30);
        $edicion->setFechaInicio(new DateTime('+10 days'));
        $edicion->setCurso($curso);
        $curso->addEdiciones($edicion);
        $this->manager->persist($edicion);

        // Creamos la entidad Formador
        $formador = new Formador();
        $formador->setNif('51515151E');
        $formador->setNombre('Víctor');
        $formador->setApellidos('Vaquero');
        $formador->setOrganizacion('USE');
        $formador->setUsuario($usuario);
        $this->manager->persist($formador);

        // Creamos una entidad FormadorEdicion
        $formadorEdicion = new FormadorEdicion();
        $formadorEdicion->setFormador($formador);
        $formadorEdicion->setEdicion($edicion);
        $this->manager->persist($formadorEdicion);

        $this->manager->flush();

        // Generamos el token CSRF
        $csrfTokenManager = static::getContainer()->get('security.csrf.token_manager');
        $token = $csrfTokenManager->getToken('delete' . $formadorEdicion->getId());

        // Realizamos la solicitud POST
        $this->client->request('POST', sprintf('%s%s', $this->path, $formadorEdicion->getId()), [
            '_token' => $token->getValue(),
        ]);

        // Verificamos la redirección
        self::assertResponseRedirects(sprintf('/intranet/forpas/gestor/formador_edicion/edicion/%s', $edicion->getId()));
    }
}