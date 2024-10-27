<?php

namespace App\Controller\Forpas;

use App\Entity\Forpas\Curso;
use App\Form\Forpas\CursoType;
use App\Repository\Forpas\CursoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controlador para gestionar los cursos formativos para el PTGAS.
 * @author Víctor M. Vaquero <vvm1002@alu.ubu.es>
 */
#[Route(path: '/intranet/forpas/gestor/curso', name: 'intranet_forpas_gestor_curso_')]
final class CursoController extends AbstractController
{
    #[Route(path: '/', name: 'index', defaults: ['titulo' => 'Listado de Cursos'], methods: ['GET'])]
    public function index(CursoRepository $cursoRepository): Response
    {
        return $this->render('intranet/forpas/gestor/curso/index.html.twig', [
            'cursos' => $cursoRepository->findAll(),
        ]);
    }
}
