<?php

namespace App\Controller\Intranet;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controlador para gestionar la redirección a la aplicación Forpas.
 * @author Víctor M. Vaquero <vvm1002@alu.ubu.es>
 */
class IndexController extends AbstractController
{
    #[Route('/', name: 'index')]
    #[Route('/intranet', name: 'intranet')]
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
