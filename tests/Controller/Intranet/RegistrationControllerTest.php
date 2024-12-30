<?php

namespace App\Tests\Controller\Intranet;

use App\Entity\Forpas\Participante;
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
        // Creamos un usuario
        $usuario = $this->createUserWithRole('ROLE_USER');

        // Creamos un participante asociado a este usuario
        $participante = new Participante();
        $participante->setNif('99999999Z');
        $participante->setNombre('Carlos');
        $participante->setApellidos('Fernández');
        $participante->setUsuario($usuario);
        $this->manager->persist($participante);

        $usuario->setParticipante($participante);
        $this->manager->persist($usuario);

        $this->manager->flush();

        // Simulamos un registro con el mismo NIF
        $crawler = $this->client->request('GET', '/intranet/register');

        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Registrar')->form();
        $form['registration_form[nif]'] = '99999999Z';
        $form['registration_form[nombre]'] = 'Nuevo';
        $form['registration_form[apellidos]'] = 'Usuario';
        $form['registration_form[email]'] = 'test@example.com';
        $form['registration_form[role]'] = 'ROLE_USER';
        $form['registration_form[username]'] = 'testUserNifExist';

        // Enviamos el formulario
        $this->client->submit($form);

        // Verificamos la redirección a la misma página (indicación de error en el registro)
        $this->assertResponseRedirects('/intranet/register');
    }
    public function testRegistrationSuccess(): void
    {
        $crawler = $this->client->request('GET', '/intranet/register');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Registrar')->form();
        $form['registration_form[nif]'] = '49520372SG';
        $form['registration_form[nombre]'] = 'Mariano';
        $form['registration_form[apellidos]'] = 'Perez';
        $form['registration_form[organizacion]'] = 'Empresa';
        $form['registration_form[email]'] = 'correo@correo.com';
        $form['registration_form[username]'] = 'mariano';
        $form['registration_form[role]'] = 'ROLE_USER';

        // Enviamos el formulario
        $this->client->submit($form);

        $crawler = $this->client->request('GET', '/intranet/login');
        $this->assertResponseIsSuccessful();
    }
    public function testRegistrationSuccessAsFormador(): void
    {
        $crawler = $this->client->request('GET', '/intranet/register');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Registrar')->form();
        $form['registration_form[nif]'] = '49520372SG';
        $form['registration_form[nombre]'] = 'Mariano';
        $form['registration_form[apellidos]'] = 'Perez';
        $form['registration_form[organizacion]'] = 'Empresa';
        $form['registration_form[email]'] = 'correo@correo.com';
        $form['registration_form[username]'] = 'mariano';
        $form['registration_form[role]'] = 'ROLE_TEACHER';

        // Enviamos el formulario
        $this->client->submit($form);

        $crawler = $this->client->request('GET', '/intranet/login');
        $this->assertResponseIsSuccessful();
    }

}
