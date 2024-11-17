<?php

namespace App\Controller\Forpas;

use App\Entity\Forpas\FormadorEdicion;
use App\Form\Forpas\FormadorEdicionType;
use App\Repository\Forpas\EdicionRepository;
use App\Repository\Forpas\FormadorEdicionRepository;
use App\Repository\Forpas\FormadorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controlador para gestionar las asignaciones de formadores a cursos del PTGAS.
 * @author Víctor M. Vaquero <vvm1002@alu.ubu.es>
 */
#[Route(path: '/intranet/forpas/gestor/formador_edicion', name: 'intranet_forpas_gestor_formador_edicion_')]
final class FormadorEdicionController extends AbstractController
{
    #[Route(path: '/edicion/{edicionId}', name: 'index', defaults: ['titulo' => 'Listado de Formadores'], methods: ['GET'])]
    public function index(int $edicionId, FormadorEdicionRepository $formadorEdicionRepository, EdicionRepository $edicionRepository): Response
    {
        // Buscamos las asignaciones filtradas por la edición específica
        $asignaciones = $formadorEdicionRepository->findBy(['edicion' => $edicionId]);
        $edicion = $edicionRepository->find($edicionId);
        return $this->render('intranet/forpas/gestor/formador_edicion/index.html.twig', [
            'formadores_edicion' => $asignaciones,
            'edicion' => $edicion,
        ]);
    }
    #[Route(path: '/new/{id}/{edicionId}', name: 'new', defaults: ['titulo' => 'Crear Nueva Asignación de Formador'], methods: ['GET', 'POST'])]
    public function new(int $id, int $edicionId, EntityManagerInterface $entityManager,
                        FormadorRepository $formadorRepository, EdicionRepository $edicionRepository): Response
    {
        // Obtenemos el formador y la edición
        $formador = $formadorRepository->find($id);
        $edicion = $edicionRepository->find($edicionId);

        $formadorEdicion = new FormadorEdicion();
        $formadorEdicion->setFormador($formador);
        $formadorEdicion->setEdicion($edicion);

        // Añadimos las asignaciones a las colecciones de Formador y Edición si no está ya presente
        if (!$formador->getFormadorEdiciones()->contains($formadorEdicion)) {
            $formador->addFormadorEdiciones($formadorEdicion);
            $entityManager->persist($formadorEdicion);
            $entityManager->flush();
        }

        if (!$edicion->getFormadoresEdicion()->contains($formadorEdicion)) {
            $edicion->addFormadoresEdicion($formadorEdicion);
        }

        $this->addFlash('success', 'Asignación de formador realizada satisfactoriamente.');
        return $this->redirectToRoute('intranet_forpas_gestor_formador_edicion_index', ['edicionId' => $edicionId], Response::HTTP_SEE_OTHER);
    }
    #[Route(path: '/{id}', name: 'show', defaults: ['titulo' => 'Datos del Formador'], methods: ['GET'])]
    public function show(FormadorEdicion $formadorEdicion): Response
    {
        return $this->render('intranet/forpas/gestor/formador_edicion/show.html.twig', [
            'formador_edicion' => $formadorEdicion,
        ]);
    }
    #[Route(path: '/{id}/edit', name: 'edit', defaults: ['titulo' => 'Editar datos sobre la asignación del Formador'], methods: ['GET', 'POST'])]
    public function edit(Request $request, FormadorEdicion $formadorEdicion, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FormadorEdicionType::class, $formadorEdicion);
        $form->handleRequest($request);
        $edicionId = $formadorEdicion->getEdicion()->getId();

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Datos modificados satisfactoriamente.');
            return $this->redirectToRoute('intranet_forpas_gestor_formador_edicion_index', ['edicionId' => $edicionId], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intranet/forpas/gestor/formador_edicion/edit.html.twig', [
            'formador_edicion' => $formadorEdicion,
            'form' => $form,
            'edicionId' => $edicionId,
        ]);
    }
    #[Route(path: '/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, FormadorEdicion $formadorEdicion, EntityManagerInterface $entityManager): Response
    {
        $edicionId = $formadorEdicion->getEdicion()->getId();

        if ($this->isCsrfTokenValid('delete'.$formadorEdicion->getId(), $request->getPayload()->getString('_token'))) {
            // Eliminamos la asignación de las colecciones de Formador y Edición
            $formador = $formadorEdicion->getFormador();
            $edicion = $formadorEdicion->getEdicion();
            $formador?->removeFormadorEdiciones($formadorEdicion);
            $edicion?->removeFormadoresEdicion($formadorEdicion);
            $entityManager->flush();
            $this->addFlash('success', 'Formador eliminado correctamente.');
        }

        return $this->redirectToRoute('intranet_forpas_gestor_formador_edicion_index', ['edicionId' => $edicionId], Response::HTTP_SEE_OTHER);
    }
}
