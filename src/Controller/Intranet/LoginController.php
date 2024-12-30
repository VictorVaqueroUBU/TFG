<?php

namespace App\Controller\Intranet;

use App\Entity\Sistema\Usuario;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Controlador para gestionar la autenticación del usuario
 * @author Víctor M. Vaquero <vvm1002@alu.ubu.es>
 */
class LoginController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route(path: '/intranet/login', name: 'intranet_login', defaults: ['titulo' => 'Inicio de Sesión'])]
    public function login (AuthenticationUtils $authenticationUtils, EntityManagerInterface $entityManager): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // Comprobamos fecha de expiración de contraseña
        $now = new DateTime();
        $passwordExpirationInterval = new DateInterval($this->getParameter('password_expiration_interval'));
        /** @var Usuario|null $user */
        $user = $this->getUser();
        if ($user && $user->getPasswordChangedAt()) {
            /** @var DateTime $passwordChangedAt */
            $passwordChangedAt = $user->getPasswordChangedAt();
            if ($passwordChangedAt instanceof DateTime) {
                $passwordExpirationDate = clone $passwordChangedAt; // Clonamos para no modificar la original
                $passwordExpirationDate->add($passwordExpirationInterval);
                if ($passwordExpirationDate < $now) {
                    $user->setVerified(false);
                    $entityManager->flush();
                }
            }
        }
        // Comprobamos activación de la cuenta del usuario o cambio de contraseña por expiración
        if ($user && !$user->isVerified()) {
            return $this->redirectToRoute('intranet_change_password');
        }

        return $this->render('/intranet/sistema/login/index.html.twig', [
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
        /** @var Usuario|null $user */
        $user = $this->getUser();

        // Verificar si el usuario ha iniciado sesión previamente para poder cambiar su contraseña
        if (!$user) {
            return $this->redirectToRoute('intranet_login');
        }
        // Verificar si el usuario ya está validado
        if ($user->isVerified()) {
            return $this->redirectToRoute('intranet_forpas');
        }
        // Realizamos el cambio de contraseña
        if ($request->isMethod('POST')) {
            $temporalPassword = $request->get('temporary_password');
            $newPassword = $request->get('new_password');
            $confirmPassword = $request->get('confirm_password');
            // Validamos la contraseña temporal
            if (!password_verify($temporalPassword, $user->getPassword())) {
                $this->addFlash('warning', 'La contraseña temporal no es correcta.');
                return $this->redirectToRoute('intranet_change_password');
            }
            // Validamos que la nueva contraseña y su confirmación coincidan
            if ($newPassword !== $confirmPassword) {
                $this->addFlash('warning', 'Las contraseñas no coinciden.');
                return $this->redirectToRoute('intranet_change_password');
            }
            // Validamos que la nueva contraseña no coincida con la antigua
            if ($temporalPassword == $newPassword) {
                $this->addFlash('warning', 'La nueva contraseña tiene que ser distinta de la antigua.');
                return $this->redirectToRoute('intranet_change_password');
            }
            // Hasheamos, activamos la cuenta, guardamos fecha de cambio de contraseña y guardamos la nueva contraseña
            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            $user->setVerified(true);
            $user->setPasswordChangedAt(new DateTime());
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Contraseña cambiada correctamente.');
            return $this->redirectToRoute('index');
        }

        return $this->render('/intranet/sistema/change-password/index.html.twig', [
            'titulo' => 'Cambiar Contraseña',
        ]);
    }
}
