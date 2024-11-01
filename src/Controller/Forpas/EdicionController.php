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
    #[Route(path: '/new/{cursoId}', name: 'new', defaults: ['titulo' => 'Crear Nueva Edición'], methods: ['GET', 'POST'])]
    public function new(Request $request, int $cursoId, EntityManagerInterface $entityManager,
                        CursoRepository $cursoRepository, EdicionRepository $edicionRepository): Response
    {
        $edicion = new Edicion();
        $curso = $cursoRepository->find($cursoId);
        $edicion->setCurso($curso);

        // Establecemos valores predeterminados
        $edicion->setSesiones(0);
        $edicion->setMaxParticipantes(0);
        $edicion->setEstado(0);

        // Obtenemos el último código de edición del curso actual
        $ultimoCodigo = $edicionRepository->findUltimaEdicionPorCurso($curso->getCodigoCurso());
        $nuevoCodigo = $ultimoCodigo ? $this->generarNuevoCodigoEdicion($curso->getCodigoCurso(), $ultimoCodigo) : $curso->getCodigoCurso() . '/00';
        $edicion->setCodigoEdicion($nuevoCodigo);

        // Comprobamos si la edición es 00 para que bloquee todos los input
        $disableFields = (substr($edicion->getCodigoEdicion(), -2) === '00');
        $form = $this->createForm(EdicionType::class, $edicion, [
            'disable_fields' => $disableFields,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($edicion);
            $entityManager->flush();
            return $this->redirectToRoute('intranet_forpas_gestor_edicion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intranet/forpas/gestor/edicion/new.html.twig', [
            'edicion' => $edicion,
            'form' => $form,
        ]);
    }
    #[Route(path: '/{id}', name: 'show', defaults: ['titulo' => 'Datos de la Edición'], methods: ['GET'])]
    public function show(Edicion $edicion): Response
    {
        return $this->render('intranet/forpas/gestor/edicion/show.html.twig', [
            'edicion' => $edicion,
        ]);
    }

    private function generarNuevoCodigoEdicion(string $codigoCurso, ?string $ultimoCodigo): string
    {
        $ultimoNumero = (int) substr($ultimoCodigo, -2);
        return $codigoCurso . '/' . str_pad(++$ultimoNumero, 2, '0', STR_PAD_LEFT);
    }
}
