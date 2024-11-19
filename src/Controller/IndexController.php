<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'index')]
    #[Route('/intranet', name: 'intranet_index')]
    public function index(): Response
    {
        if ($this->getUser()) {
            // Usuario autenticado, redirigir al portal de Forpas
            return $this->redirectToRoute('intranet_forpas');
        }

        // Usuario no autenticado, redirigir al login
        return $this->redirectToRoute('intranet_login');
    }
}
