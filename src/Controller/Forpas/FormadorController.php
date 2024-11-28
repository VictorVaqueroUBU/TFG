<?php

namespace App\Controller\Forpas;

use App\Entity\Forpas\Formador;
use App\Form\Forpas\FormadorType;
use App\Repository\Forpas\EdicionRepository;
use App\Repository\Forpas\FormadorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controlador para gestionar los formadores del PTGAS.
 * @author Víctor M. Vaquero <vvm1002@alu.ubu.es>
 */
#[Route(path: '/intranet/forpas/gestor/formador', name: 'intranet_forpas_gestor_formador_')]
final class FormadorController extends AbstractController
{
    #[Route(path: '/', name: 'index', defaults: ['titulo' => 'Listado de Formadores'], methods: ['GET'])]
    public function index(FormadorRepository $formadorRepository): Response
    {
        return $this->render('intranet/forpas/gestor/formador/index.html.twig', [
            'formadores' => $formadorRepository->findAll(),
        ]);
    }
    #[Route(path: '/new', name: 'new', defaults: ['titulo' => 'Crear Nuevo Formador'], methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $formador = new Formador();
        $form = $this->createForm(FormadorType::class, $formador);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($formador);
            $entityManager->flush();
            $this->addFlash('success', 'El alta del Formador se ha realizado satisfactoriamente.');
            return $this->redirectToRoute('intranet_forpas_gestor_formador_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intranet/forpas/gestor/formador/new.html.twig', [
            'formador' => $formador,
            'form' => $form,
        ]);
    }
    #[Route(path: '/append/{id}', name: 'append', defaults: ['titulo' => 'Añadir Formador'], methods: ['GET'])]
    public function append(int $id, EdicionRepository $edicionRepository, FormadorRepository $formadorRepository): Response
    {
        // Obtenemos la edición actual
        $edicion = $edicionRepository->find($id);

        // Buscamos los formadores seleccionables
        $formadores = $formadorRepository->findPossibleTeacher($edicion);

        return $this->render('intranet/forpas/gestor/formador/append.html.twig', [
            'formadores_posibles' => $formadores,
            'edicion' => $edicion,
        ]);
    }
    #[Route(path: '/{id}', name: 'show', defaults: ['titulo' => 'Datos del Formador'], methods: ['GET'])]
    public function show(Formador $formador): Response
    {
        return $this->render('intranet/forpas/gestor/formador/show.html.twig', [
            'formador' => $formador,
        ]);
    }
    #[Route(path: '/{id}/edit', name: 'edit', defaults: ['titulo' => 'Editar Formador'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Formador $formador, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FormadorType::class, $formador);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Los datos del formador se han modificado satisfactoriamente.');
            return $this->redirectToRoute('intranet_forpas_gestor_formador_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intranet/forpas/gestor/formador/edit.html.twig', [
            'formador' => $formador,
            'form' => $form,
        ]);
    }
    #[Route(path: '/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Formador $formador, EntityManagerInterface $entityManager): Response
    {
        // Verificamos si el formador tiene ediciones asociadas
        if (!$formador->getFormadorEdiciones()->isEmpty()) {
            // Si tiene ediciones, redirige con un mensaje de error
            $this->addFlash('danger', 'No se puede eliminar al formador porque tiene ediciones asociadas.');
            return $this->redirectToRoute('intranet_forpas_gestor_formador_index');
        }

        if ($this->isCsrfTokenValid('delete'.$formador->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($formador);
            $entityManager->flush();
            $this->addFlash('success', 'Formador eliminado correctamente.');
        }

        return $this->redirectToRoute('intranet_forpas_gestor_formador_index', [], Response::HTTP_SEE_OTHER);
    }
}
