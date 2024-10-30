<?php

namespace App\Controller\Forpas;

use App\Entity\Forpas\Curso;
use App\Entity\Forpas\Edicion;
use App\Form\Forpas\EdicionType;
use App\Repository\Forpas\CursoRepository;
use App\Repository\Forpas\EdicionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controlador para gestionar las ediciones de los cursos formativos para el PTGAS.
 * @author Víctor M. Vaquero <vvm1002@alu.ubu.es>
 */
#[Route(path: '/intranet/forpas/gestor/edicion', name: 'intranet_forpas_gestor_edicion_')]
final class EdicionController extends AbstractController
{
    #[Route(path: '/', name: 'index', defaults: ['titulo' => 'Listado de ediciones'], methods: ['GET'])]
    public function index(Request $request, EdicionRepository $edicionRepository, CursoRepository $cursoRepository): Response
    {
        $cursoId = $request->query->get('cursoId');
        if ($cursoId) {
            $ediciones = $edicionRepository->findByCurso($cursoId);
            $curso = $cursoRepository->find($cursoId);
        } else {
            $ediciones = $edicionRepository->findAllWithCursos();
            $curso = null;
        }

        return $this->render('intranet/forpas/gestor/edicion/index.html.twig', [
            'ediciones' => $ediciones,
            'curso' => $curso,
        ]);
    }
}
