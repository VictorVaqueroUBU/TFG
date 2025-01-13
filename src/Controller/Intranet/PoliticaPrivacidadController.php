<?php

namespace App\Controller\Intranet;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controlador para informar de la política de privacidad de la aplicación
 * @author Víctor M. Vaquero <vvm1002@alu.ubu.es>
 */
class PoliticaPrivacidadController extends AbstractController
{
    #[Route('/intranet/politica-privacidad', name: 'politica_privacidad', defaults: ['titulo' => 'Política de Privacidad'])]
    public function index(): Response
    {
        return $this->render('intranet/sistema/politica_privacidad/index.html.twig', []);
    }
}
