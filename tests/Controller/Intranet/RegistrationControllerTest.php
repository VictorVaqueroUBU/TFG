<?php

namespace App\Tests\Controller\Intranet;

use App\Entity\Forpas\Formador;
use App\Entity\Forpas\Participante;
use App\Entity\Sistema\Usuario;
use App\Tests\Controller\Forpas\BaseControllerTest;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

class RegistrationControllerTest extends BaseControllerTest
{
    protected EntityManagerInterface $entityManager;
    protected function setUp(): void
    {
        parent::setUp();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();

        // Limpieza de la base de datos
        $schemaTool = new SchemaTool($this->entityManager);
        $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);
    }
    public function testRedirectsIfAuthenticated(): void
    {
        // Creamos un usuario autenticado
        $user = $this->createUserWithRole('ROLE_USER');
        $this->client->loginUser($user);

        // Accedemos a la ruta de registro
        $this->client->request('GET', '/intranet/register');

        // Verificamos la redirección a la ruta esperada
        $this->assertResponseRedirects('/intranet');
    }
    public function testRegistrationFailsIfNifExists(): void
    {
        // Creamos un usuario y participante con el mismo NIF
        $usuario = $this->createUserWithRole('ROLE_USER');
        $participant = new Participante();
        $participant->setNif('12345678A');
        $participant->setNombre('Nombre');
        $participant->setApellidos('Apellido');
        $participant->setUsuario($usuario);
        $this->entityManager->persist($participant);
        $this->entityManager->flush();

        // Simulamos un registro con el mismo NIF
        $crawler = $this->client->request('GET', '/intranet/register');
        $form = $crawler->selectButton('Registrar')->form();
        $form['registration_form[nif]'] = '12345678A';
        $form['registration_form[nombre]'] = 'Nuevo';
        $form['registration_form[apellidos]'] = 'Usuario';
        $form['registration_form[email]'] = 'test@example.com';
        $form['registration_form[role]'] = 'ROLE_USER';
        $form['registration_form[username]'] = 'test_user';

        // Enviamos el formulario
        $this->client->submit($form);

        // Verificamos la redirección a la misma página (indicación de error en el registro)
        $this->assertResponseRedirects('/intranet/register');
    }
    public function testRegistrationSuccess(): void
    {
        // Simulamos un registro exitoso
        $crawler = $this->client->request('GET', '/intranet/register');
        $form = $crawler->selectButton('Registrar')->form();
        $form['registration_form[nif]'] = '98765432B';
        $form['registration_form[nombre]'] = 'Nuevo';
        $form['registration_form[apellidos]'] = 'Usuario';
        $form['registration_form[email]'] = 'success@example.com';
        $form['registration_form[role]'] = 'ROLE_USER';
        $form['registration_form[username]'] = 'success_user';

        // Enviamos el formulario
        $this->client->submit($form);

        // Verificamos la redirección a la página principal tras el registro exitoso
        $this->assertResponseRedirects('/');
    }
    public function testRegistrationSuccessAsFormador(): void
    {
        // Simulamos un registro exitoso con ROLE_FORMADOR
        $crawler = $this->client->request('GET', '/intranet/register');
        $form = $crawler->selectButton('Registrar')->form();

        // Rellenamos los campos del formulario
        $form['registration_form[nif]'] = '11223344C';
        $form['registration_form[nombre]'] = 'Nuevo';
        $form['registration_form[apellidos]'] = 'Formador';
        $form['registration_form[organizacion]'] = 'UBU';
        $form['registration_form[email]'] = 'formador@example.com';
        $form['registration_form[username]'] = 'formador_user';
        $form['registration_form[role]'] = 'ROLE_TEACHER'; // Seleccionamos el rol de FORMADOR

        // Enviamos el formulario
        $this->client->submit($form);

        // Verificamos la redirección a la página principal tras el registro exitoso
        $this->assertResponseRedirects('/');

        // Verificamos que el usuario y la entidad Formador se hayan creado en la base de datos
        $userRepository = $this->entityManager->getRepository(Usuario::class);
        $formadorRepository = $this->entityManager->getRepository(Formador::class);

        $user = $userRepository->findOneBy(['email' => 'formador@example.com']);
        $this->assertNotNull($user, 'El usuario no se ha creado correctamente.');
        $this->assertEquals('ROLE_TEACHER', $user->getRoles()[0], 'El rol no se ha asignado correctamente.');

        // Verificamos la relación con Formador
        $formador = $formadorRepository->findOneBy(['nif' => '11223344C']);
        $this->assertNotNull($formador, 'La entidad Formador no se ha creado.');
        $this->assertSame($user, $formador->getUsuario(), 'La relación entre Usuario y Formador no es bidireccional.');
    }

}
