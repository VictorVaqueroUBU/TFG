<?php

namespace App\Tests\Repository\Sistema;

use App\Entity\Sistema\Usuario;
use App\Repository\Sistema\UsuarioRepository;
use App\Tests\Controller\Forpas\BaseControllerTest;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class UsuarioRepositoryTest extends BaseControllerTest
{
    private UsuarioRepository $usuarioRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // Usa el $manager proporcionado por la clase base
        $this->usuarioRepository = $this->manager->getRepository(Usuario::class);

        // Limpiar y preparar la base de datos en cada test
        $schemaTool = new SchemaTool($this->manager);
        $classes = $this->manager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);
    }
    public function testUpgradePasswordSuccessfully(): void
    {
        // Crear un usuario con un username válido
        $usuario = new Usuario();
        $usuario->setEmail('test@example.com');
        $usuario->setUsername('test_user'); // Añadir username para evitar el error
        $usuario->setPassword('old_password');
        $usuario->setVerified(false);
        $usuario->setCreatedAt(new \DateTimeImmutable());

        // Persistir el usuario
        $this->manager->persist($usuario);
        $this->manager->flush();

        // Actualizar la contraseña
        $newPassword = 'new_hashed_password';
        $this->usuarioRepository->upgradePassword($usuario, $newPassword);

        // Verificar que la contraseña se actualizó
        $this->manager->refresh($usuario);
        $this->assertSame($newPassword, $usuario->getPassword());
    }

    public function testUpgradePasswordThrowsExceptionForUnsupportedUser(): void
    {
        $this->expectException(UnsupportedUserException::class);

        // Crear un usuario que no es compatible
        $unsupportedUser = $this->createMock(\Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface::class);

        $this->usuarioRepository->upgradePassword($unsupportedUser, 'new_hashed_password');
    }
}
