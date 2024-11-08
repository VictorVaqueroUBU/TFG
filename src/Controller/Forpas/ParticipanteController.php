<?php

namespace App\Controller\Forpas;

use App\Entity\Forpas\Participante;
use App\Form\Forpas\ParticipanteType;
use App\Repository\Forpas\EdicionRepository;
use App\Repository\Forpas\ParticipanteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controlador para gestionar los participantes del PTGAS.
 * @author Víctor M. Vaquero <vvm1002@alu.ubu.es>
 */
#[Route(path: '/intranet/forpas/gestor/participante', name: 'intranet_forpas_gestor_participante_')]
final class ParticipanteController extends AbstractController
{
    #[Route(path: '/', name: 'index', defaults: ['titulo' => 'Listado de Participantes'], methods: ['GET'])]
    public function index(ParticipanteRepository $participanteRepository): Response
    {
        return $this->render('intranet/forpas/gestor/participante/index.html.twig', [
            'participantes' => $participanteRepository->findAll(),
        ]);
    }
    #[Route(path: '/new', name: 'new', defaults: ['titulo' => 'Crear Nuevo Participante'], methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $participante = new participante();
        $form = $this->createForm(ParticipanteType::class, $participante);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($participante);
            $entityManager->flush();
            $this->addFlash('success', 'El alta del participante se ha realizado satisfactoriamente.');
            return $this->redirectToRoute('intranet_forpas_gestor_participante_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intranet/forpas/gestor/participante/new.html.twig', [
            'participante' => $participante,
            'form' => $form,
        ]);
    }
    #[Route(path: '/append/{id}', name: 'append', defaults: ['titulo' => 'Seleccionar Participante'], methods: ['GET'])]
    public function append(int $id, EdicionRepository $edicionRepository, ParticipanteRepository $participanteRepository): Response
    {
        // Obtenemos la edición actual
        $edicion = $edicionRepository->find($id);

        // Buscamos los participantes seleccionables
        $participantes = $participanteRepository->findPossibleEntries($edicion);

        return $this->render('intranet/forpas/gestor/participante/append.html.twig', [
            'participantes_posibles' => $participantes,
            'edicion' => $edicion,
        ]);
    }
    #[Route(path: '/{id}', name: 'show', defaults: ['titulo' => 'Datos del Participante'], methods: ['GET'])]
    public function show(Participante $participante): Response
    {
        return $this->render('intranet/forpas/gestor/participante/show.html.twig', [
            'participante' => $participante,
        ]);
    }
}
