<?php

namespace App\Controller\Intranet;

use App\Repository\Forpas\CursoRepository;
use App\Repository\Forpas\EdicionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controlador para gestionar el acceso a la aplicación Forpas.
 * @author Víctor M. Vaquero <vvm1002@alu.ubu.es>
 */
#[Route(path: '/intranet/forpas', name: 'intranet_forpas')]
class ForpasController extends AbstractController
{
    #[Route(path: '/', name: '', defaults: ['titulo' => 'Portal de Forpas'])]
    public function inicio(): Response
    {
        return $this->render("intranet/forpas/index.html.twig");
    }

    // TODO: Aquí crearemos una función por cada Rol (Gestor, Usuario, Formador)
    #[Route(path: '/gestor', name: '_gestor', defaults: ['titulo' => 'Portal del Gestor'])]
    public function forpasGestor(CursoRepository $cursoRepository, EdicionRepository $edicionRepository): Response
    {
        //$this->denyAccessUnlessGranted('gestor');
        return $this->render('intranet/forpas/gestor/index.html.twig', [
            'cursos' => $cursoRepository->findAll(),
            'ediciones' => $edicionRepository->findAll(),
        ]);
    }
}
