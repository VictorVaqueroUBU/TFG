<?php
namespace App\Tests\Controller\Forpas;

use App\Entity\Sistema\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseControllerTest extends WebTestCase
{
    protected EntityManagerInterface $manager;
    protected KernelBrowser $client;


    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
    }
    protected function createUserWithRole(string $role): Usuario
    {
        $username = 'test_user_' . strtolower($role); // Nombre único basado en el rol
        $userRepository = $this->manager->getRepository(Usuario::class);

        // Busca si el usuario ya existe
        $existingUser = $userRepository->findOneBy(['username' => $username]);
        if ($existingUser) {
            return $existingUser;
        }

        // Crea un nuevo usuario
        $user = new Usuario();
        $user->setEmail($username . '@example.com');
        $user->setUsername($username);
        $user->setPassword(password_hash('password', PASSWORD_BCRYPT));
        $user->setRoles([$role]);
        $user->setVerified(true);
        $user->setCreatedAt(new \DateTimeImmutable('now'));

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }
}
