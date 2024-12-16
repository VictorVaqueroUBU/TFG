<?php

namespace App\Tests\Controller\Intranet;

use App\Tests\Controller\Forpas\BaseControllerTest;

class ForpasControllerTest extends BaseControllerTest
{
    public function testInicioPageAccessAsUser(): void
    {
        $user = $this->createUserWithRole('ROLE_USER');
        $this->client->loginUser($user);

        $this->client->request('GET', '/intranet/forpas/');
        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h5', 'Portal del Participante');
        $this->assertSelectorExists('a[href="/intranet/forpas/participante"]');
        $this->assertSelectorNotExists('a[href="/intranet/forpas/gestor"]');
        $this->assertSelectorNotExists('a[href="/intranet/forpas/formador"]');
    }
    public function testInicioPageAccessAsAdmin(): void
    {
        $user = $this->createUserWithRole('ROLE_ADMIN');
        $this->client->loginUser($user);

        $this->client->request('GET', '/intranet/forpas/');
        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h5', 'Portal del Gestor');
        $this->assertSelectorExists('a[href="/intranet/forpas/gestor"]');
        $this->assertSelectorNotExists('a[href="/intranet/forpas/participante"]');
        $this->assertSelectorNotExists('a[href="/intranet/forpas/formador"]');
    }
    public function testInicioPageAccessAsTeacher(): void
    {
        $user = $this->createUserWithRole('ROLE_TEACHER');
        $this->client->loginUser($user);

        $this->client->request('GET', '/intranet/forpas/');
        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h5', 'Portal del Formador');
        $this->assertSelectorExists('a[href="/intranet/forpas/formador"]');
        $this->assertSelectorNotExists('a[href="/intranet/forpas/participante"]');
        $this->assertSelectorNotExists('a[href="/intranet/forpas/gestor"]');
    }
    public function testAccessParticipanteWithUserRole(): void
    {
        $user = $this->createUserWithRole('ROLE_USER');
        $this->client->loginUser($user);

        $this->client->request('GET', '/intranet/forpas/participante');
        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('div.pagina-titulo', 'Portal del Participante');
    }
    public function testAccessDeniedForUserRole(): void
    {
        $user = $this->createUserWithRole('ROLE_USER');
        $this->client->loginUser($user);

        $this->client->request('GET', '/intranet/forpas/gestor');
        $this->assertResponseRedirects('/intranet/forpas/'); // Verifica la redirección
        $this->client->request('GET', '/intranet/forpas/formador');
        $this->assertResponseRedirects('/intranet/forpas/'); // Verifica la redirección
    }
    public function testAccessGestorWithAdminRole(): void
    {
        $user = $this->createUserWithRole('ROLE_ADMIN');
        $this->client->loginUser($user);

        $this->client->request('GET', '/intranet/forpas/gestor');
        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('div.pagina-titulo', 'Portal del Gestor');
    }
    public function testAccessDeniedForAdminRole(): void
    {
        $user = $this->createUserWithRole('ROLE_ADMIN');
        $this->client->loginUser($user);

        $this->client->request('GET', '/intranet/forpas/participante');
        $this->assertResponseRedirects('/intranet/forpas/'); // Verifica la redirección
        $this->client->request('GET', '/intranet/forpas/formador');
        $this->assertResponseRedirects('/intranet/forpas/'); // Verifica la redirección
    }
    public function testAccessFormadorWithTeacherRole(): void
    {
        $user = $this->createUserWithRole('ROLE_TEACHER');
        $this->client->loginUser($user);

        $this->client->request('GET', '/intranet/forpas/formador');
        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('div.pagina-titulo', 'Portal del Formador');
    }
    public function testAccessDeniedForTeacherRole(): void
    {
        $user = $this->createUserWithRole('ROLE_TEACHER');
        $this->client->loginUser($user);

        $this->client->request('GET', '/intranet/forpas/gestor');
        $this->assertResponseRedirects('/intranet/forpas/'); // Verifica la redirección
        $this->client->request('GET', '/intranet/forpas/participante');
        $this->assertResponseRedirects('/intranet/forpas/'); // Verifica la redirección
    }
}
