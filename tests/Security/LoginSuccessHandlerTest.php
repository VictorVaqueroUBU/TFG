<?php

namespace App\Tests\Security;

use App\Security\LoginSuccessHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Tests\Controller\Forpas\BaseControllerTest;

class LoginSuccessHandlerTest extends BaseControllerTest
{
    public function testRedirectsToChangePasswordIfUserNotVerified(): void
    {
        // Configuramos el mock del generador de URLs
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with('intranet_change_password')
            ->willReturn('/intranet/change-password');

        // Instancia del manejador
        $handler = new LoginSuccessHandler($urlGenerator);

        // Crear un usuario no verificado utilizando la clase base
        $user = $this->createUserWithRole('ROLE_USER');
        $user->setVerified(false); // Aseguramos que no está verificado

        // Mock del token de autenticación
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        // Mock de la solicitud
        $request = $this->createMock(Request::class);

        // Ejecución
        $response = $handler->onAuthenticationSuccess($request, $token);

        // Verificación
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/intranet/change-password', $response->getTargetUrl());
    }

    public function testRedirectsToIntranetForpasIfUserVerified(): void
    {
        // Configuramos el mock del generador de URLs
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with('intranet_forpas')
            ->willReturn('/intranet/forpas');

        // Instancia del manejador
        $handler = new LoginSuccessHandler($urlGenerator);

        // Crear un usuario verificado utilizando la clase base
        $user = $this->createUserWithRole('ROLE_USER');
        $user->setVerified(true); // Aseguramos que está verificado

        // Mock del token de autenticación
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        // Mock de la solicitud
        $request = $this->createMock(Request::class);

        // Ejecución
        $response = $handler->onAuthenticationSuccess($request, $token);

        // Verificación
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/intranet/forpas', $response->getTargetUrl());
    }
}
