<?php

namespace App\Controller\Intranet;

use App\Repository\Forpas\CursoRepository;
use App\Repository\Forpas\EdicionRepository;
use App\Repository\Forpas\FormadorRepository;
use App\Repository\Forpas\ParticipanteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controlador para gestionar el acceso a las distintas aplicaciones de Forpas.
 * @author Víctor M. Vaquero <vvm1002@alu.ubu.es>
 */
#[Route(path: '/intranet/forpas', name: 'intranet_forpas')]
class ForpasController extends AbstractController
{
    #[Route(path: '/', name: '', defaults: ['titulo' => 'Servicio de Formación'])]
    public function inicio(): Response
    {
        $accesos = [];

        if ($this->isGranted('ROLE_USER')) {
            $accesos[] = [
                'nombre' => 'Portal del Participante',
                'ruta' => $this->generateUrl('intranet_forpas_participante'),
                'icono' => 'fas fa-user-graduate',
            ];
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            $accesos[] = [
                'nombre' => 'Portal del Gestor',
                'ruta' => $this->generateUrl('intranet_forpas_gestor'),
                'icono' => 'fas fa-tools',
            ];
        }

        if ($this->isGranted('ROLE_TEACHER')) {
            $accesos[] = [
                'nombre' => 'Portal del Formador',
                'ruta' => $this->generateUrl('intranet_forpas_formador'),
                'icono' => 'fas fa-chalkboard-teacher',
            ];
        }

        return $this->render('intranet/forpas/index.html.twig', [
            'accesos' => $accesos,
        ]);
    }
    #[Route(path: '/gestor', name: '_gestor', defaults: ['titulo' => 'Gestión de Entidades'])]
    public function forpasGestor(CursoRepository $cursoRepository, EdicionRepository $edicionRepository,
                                 ParticipanteRepository $participanteRepository, FormadorRepository $formadorRepository): Response
    {
        //$this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('intranet/forpas/gestor/index.html.twig', [
            'cursos' => $cursoRepository->findAll(),
            'ediciones' => $edicionRepository->findAll(),
            'participantes' => $participanteRepository->findAll(),
            'formadores' => $formadorRepository->findAll(),
        ]);
    }
    #[Route(path: '/participante', name: '_participante', defaults: ['titulo' => 'Acciones disponibles'])]
    public function forpasParticipante(): Response
    {
        return $this->render('intranet/forpas/participante/index.html.twig');
    }
    #[Route(path: '/formador', name: '_formador', defaults: ['titulo' => 'Acciones disponibles'])]
    public function forpasFormador(): Response
    {
        return $this->render('intranet/forpas/formador/index.html.twig');
    }
}
