<?php

namespace App\Tests\Controller\Intranet;

use App\Tests\Controller\Forpas\BaseControllerTest;
use DateTime;

class LoginControllerTest extends BaseControllerTest
{
    public function testLoginPageLoads(): void
    {
        $this->client->request('GET', '/intranet/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form', 'El formulario de inicio de sesión no está presente.');
    }
    public function testRedirectToChangePasswordIfExpired(): void
    {
        $user = $this->createUserWithRole('ROLE_USER');
        $user->setPasswordChangedAt((new DateTime())->modify('-1 year -1 day')); // Simula contraseña expirada
        $this->manager->persist($user);
        $this->manager->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/intranet/login');

        $this->assertResponseRedirects('/intranet/change-password');
    }
    public function testChangePasswordSuccess(): void
    {
        $user = $this->createUserWithRole('ROLE_USER');
        $user->setVerified(false); // Simula cuenta no verificada
        $user->setPassword(password_hash('old_password', PASSWORD_BCRYPT));
        $this->manager->persist($user);
        $this->manager->flush();

        $this->client->loginUser($user);
        $this->client->request('POST', '/intranet/change-password', [
            'temporary_password' => 'old_password',
            'new_password' => 'new_password',
            'confirm_password' => 'new_password',
        ]);

        $this->assertResponseRedirects('/');

        $updatedUser = $this->manager->getRepository(get_class($user))->find($user->getId());
        $this->assertTrue($updatedUser->isVerified());
    }
    public function testChangePasswordFailsIfTemporaryPasswordIsInvalid(): void
    {
        // Creamos un usuario simulado
        $user = $this->createUserWithRole('ROLE_USER');
        $user->setPassword(password_hash('old_password', PASSWORD_BCRYPT)); // Contraseña actual
        $user->setVerified(false); // Simula que necesita verificación
        $user->setPasswordChangedAt(new DateTime('-1 year')); // Simulamos contraseña expirada
        $this->manager->persist($user);
        $this->manager->flush();

        // Logueamos al usuario
        $this->client->loginUser($user);

        // Enviamos petición al endpoint con contraseña temporal inválida
        $this->client->request('POST', '/intranet/change-password', [
            'temporary_password' => 'wrong_password', // Contraseña temporal incorrecta
            'new_password' => 'new_password', // Nueva contraseña válida
            'confirm_password' => 'new_password', // Confirmación válida
        ]);

        // Comprobamos la redirección a la misma página
        $this->assertResponseRedirects('/intranet/change-password');

        // Seguimos la redirección para comprobar el contenido
        $crawler = $this->client->followRedirect();

        // Depuramos el contenido de la página en caso de fallo
        if (!$crawler->filter('.alert-warning')->count()) {
            echo $crawler->filter('body')->text(); // Imprime el contenido de la página para depuración
        }

        // Verificar que el mensaje flash se muestra correctamente
        $this->assertSelectorTextContains(
            '.alert-warning',
            'La contraseña temporal no es correcta.'
        );
    }
}
