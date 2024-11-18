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
    public function index(Request $request, CursoRepository $cursoRepository): Response
    {
        $year = $request->query->get('year', date('Y')); // Obtiene el año actual si no se selecciona ninguno
        $cursos = $cursoRepository->findByYear($year);

        return $this->render('intranet/forpas/gestor/curso/index.html.twig', [
            'cursos' => $cursos,
            'year' => $year,
        ]);
    }
    #[Route(path: '/new', name: 'new', defaults: ['titulo' => 'Crear Nuevo Curso'], methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, CursoRepository $cursoRepository): Response
    {
        $curso = new Curso();
        $year = (int) date('Y');
        $primerCodigoLibre = $cursoRepository->findPrimerCodigoCursoLibre($year);
        $curso->setCodigoCurso($primerCodigoLibre);

        $form = $this->createForm(CursoType::class, $curso);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($curso);
            $entityManager->flush();
            $this->addFlash('success', 'La creación del curso se ha realizada satisfactoriamente.');
            return $this->redirectToRoute('intranet_forpas_gestor_curso_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intranet/forpas/gestor/curso/new.html.twig', [
            'curso' => $curso,
            'form' => $form,
        ]);
    }
    #[Route(path: '/{id}', name: 'show', defaults: ['titulo' => 'Datos del Curso'], methods: ['GET'])]
    public function show(Curso $curso): Response
    {
        return $this->render('intranet/forpas/gestor/curso/show.html.twig', [
            'curso' => $curso,
        ]);
    }
    #[Route(path: '/{id}/edit', name: 'edit', defaults: ['titulo' => 'Editar Curso'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Curso $curso, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CursoType::class, $curso);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Los datos del curso se han modificado satisfactoriamente.');
            return $this->redirectToRoute('intranet_forpas_gestor_curso_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intranet/forpas/gestor/curso/edit.html.twig', [
            'curso' => $curso,
            'form' => $form,
        ]);
    }
    #[Route(path: '/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Curso $curso, EntityManagerInterface $entityManager): Response
    {
        // Verificamos si el curso tiene ediciones asociadas
        if (!$curso->getEdiciones()->isEmpty()) {
            // Si tiene ediciones, redirige con un mensaje de error
            $this->addFlash('warning', 'No se puede eliminar el curso porque tiene ediciones creadas.');
            return $this->redirectToRoute('intranet_forpas_gestor_curso_index');
        }

        if ($this->isCsrfTokenValid('delete'.$curso->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($curso);
            $entityManager->flush();
            $this->addFlash('success', 'Curso eliminado correctamente.');
        }

        return $this->redirectToRoute('intranet_forpas_gestor_curso_index', [], Response::HTTP_SEE_OTHER);
    }
}
