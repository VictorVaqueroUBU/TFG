<?php

namespace App\Controller\Forpas;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use LogicException;

class LoginController extends AbstractController
{
    #[Route(path: '/intranet/login', name: 'intranet_login', defaults: ['titulo' => 'Inicio de Sesión'])]
    public function login (AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($this->getUser() && !$this->getUser()->isVerified()) {
            return $this->redirectToRoute('intranet_change_password');
        }

        return $this->render('/intranet/login/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
    #[Route(path: '/logout', name: 'logout')]
    public function logout(): void
    {
        throw new LogicException();
    }
    #[Route(path: '/intranet/change-password', name: 'intranet_change_password', defaults: ['titulo' => 'Cambio de contraseña'])]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('intranet_login');
        }

        // Verificar si el usuario ya está validado
        if ($user->isVerified()) {
            return $this->redirectToRoute('intranet_index');
        }

        if ($request->isMethod('POST')) {
            $temporalPassword = $request->get('temporary_password');
            $newPassword = $request->get('new_password');
            $confirmPassword = $request->get('confirm_password');

            // Validar la contraseña temporal
            if (!password_verify($temporalPassword, $user->getPassword())) {
                $this->addFlash('warning', 'La contraseña temporal no es correcta.');
                return $this->redirectToRoute('intranet_change_password');
            }

            // Validar que la nueva contraseña y su confirmación coincidan
            if ($newPassword !== $confirmPassword) {
                $this->addFlash('warning', 'Las contraseñas no coinciden.');
                return $this->redirectToRoute('intranet_change_password');
            }

            // Hashear y guardar la nueva contraseña
            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            $user->setVerified(true);

            $entityManager->flush();

            $this->addFlash('success', 'Contraseña cambiada correctamente.');
            return $this->redirectToRoute('index');
        }

        return $this->render('intranet/change-password/index.html.twig', [
            'titulo' => 'Cambiar Contraseña',
        ]);
    }
}
