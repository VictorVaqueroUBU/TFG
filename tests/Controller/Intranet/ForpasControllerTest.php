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
    }
    public function testInicioPageAccessAsAdmin(): void
    {
        $admin = $this->createUserWithRole('ROLE_ADMIN');
        $this->client->loginUser($admin);

        $this->client->request('GET', '/intranet/forpas/');
        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h5', 'Portal del Gestor');
        $this->assertSelectorExists('a[href="/intranet/forpas/gestor"]');
        $this->assertSelectorNotExists('a[href="/intranet/forpas/participante"]');
    }
    public function testAccessGestorWithAdminRole(): void
    {
        $admin = $this->createUserWithRole('ROLE_ADMIN');
        $this->client->loginUser($admin);

        $this->client->request('GET', '/intranet/forpas/gestor');
        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('div.pagina-titulo', 'Portal del Gestor');
    }
    public function testAccessGestorDeniedForUserRole(): void
    {
        $user = $this->createUserWithRole('ROLE_USER');
        $this->client->loginUser($user);

        $this->client->request('GET', '/intranet/forpas/gestor');
        $this->assertResponseRedirects('/intranet/forpas/'); // Verifica la redirección
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
    public function testAccessParticipanteDeniedForAdminRole(): void
    {
        $admin = $this->createUserWithRole('ROLE_ADMIN');
        $this->client->loginUser($admin);

        $this->client->request('GET', '/intranet/forpas/participante');
        $response = $this->client->getResponse();

        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-warning', 'No tienes permiso para acceder a esta página.');

    }
}
