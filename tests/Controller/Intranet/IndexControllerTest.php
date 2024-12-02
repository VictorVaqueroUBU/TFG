<?php

namespace App\Tests\Controller\Intranet;

use App\Tests\Controller\Forpas\BaseControllerTest;

class IndexControllerTest extends BaseControllerTest
{
    public function testIndexRedirectsToForpasWhenAuthenticated(): void
    {
        // Creamos un cliente con un usuario autenticado
        $user = $this->createUserWithRole('ROLE_USER');
        $this->client->loginUser($user);

        // Solicitamos la ruta '/'
        $this->client->request('GET', '/');
        $this->assertResponseRedirects('/intranet/forpas/', 302);
    }
    public function testIndexRedirectsToLoginWhenNotAuthenticated(): void
    {
        // Sin autenticación
        $this->client->request('GET', '/');
        $this->assertResponseRedirects('/intranet/login');
    }
}