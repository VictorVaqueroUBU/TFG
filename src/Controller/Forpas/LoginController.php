<?php

namespace App\Controller\Forpas;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route(path: '/intranet/login', name: 'intranet_login')]
    public function login (AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('/intranet/login/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error ,
            'titulo' => 'Inicio de Sesión'
        ]);
    }
    #[Route(path: '/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException( 'this method can be blank it will be intercepted by the logout key on your firewall');
    }
}
