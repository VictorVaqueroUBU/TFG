<?php

namespace App\Controller\Forpas;

use App\Entity\Forpas\ParticipanteEdicion;
use App\Form\Forpas\ParticipanteEdicionType;
use App\Repository\Forpas\EdicionRepository;
use App\Repository\Forpas\ParticipanteEdicionRepository;
use App\Repository\Forpas\ParticipanteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use DateTime;

/**
 * Controlador para gestionar las inscripciones de los participantes del PTGAS.
 * @author Víctor M. Vaquero <vvm1002@alu.ubu.es>
 */
#[Route(path: '/intranet/forpas/gestor/participante_edicion', name: 'intranet_forpas_gestor_participante_edicion_')]
final class ParticipanteEdicionController extends AbstractController
{
    #[Route(path: '/edicion/{edicionId}', name: 'index', defaults: ['titulo' => 'Listado de Inscripciones'], methods: ['GET'])]
    public function index(int $edicionId, ParticipanteEdicionRepository $participanteEdicionRepository, EdicionRepository $edicionRepository): Response
    {
        // Buscamos las inscripciones filtradas por la edición específica
        $inscripciones = $participanteEdicionRepository->findBy(['edicion' => $edicionId]);
        $edicion = $edicionRepository->find($edicionId);
        return $this->render('intranet/forpas/gestor/participante_edicion/index.html.twig', [
            'participantes_edicion' => $inscripciones,
            'edicion' => $edicion,
        ]);
    }
    #[Route(path: '/new/{id}/{edicionId}', name: 'new', defaults: ['titulo' => 'Crear Nueva Inscripción'], methods: ['GET', 'POST'])]
    public function new(int $id, int $edicionId, EntityManagerInterface $entityManager,
                        ParticipanteRepository $participanteRepository, EdicionRepository $edicionRepository): Response
    {
        // Obtenemos el participante y la edición
        $participante = $participanteRepository->find($id);
        $edicion = $edicionRepository->find($edicionId);

        // Creamos la relación ParticipanteEdicion
        $participanteEdicion = new ParticipanteEdicion();
        $participanteEdicion->setParticipante($participante);
        $participanteEdicion->setEdicion($edicion);
        $participanteEdicion->setFechaSolicitud(new DateTime());

        // Añadimos la inscripción a las colecciones
        $participante->addParticipanteEdiciones($participanteEdicion);
        $edicion->addParticipantesEdicion($participanteEdicion);

        // Persistimos la inscripción
        $entityManager->persist($participanteEdicion);
        $entityManager->flush();

        $this->addFlash('success', 'Inscripción realizada satisfactoriamente.');
        return $this->redirectToRoute('intranet_forpas_gestor_participante_edicion_index', ['edicionId' => $edicionId], Response::HTTP_SEE_OTHER);
    }
    #[Route(path: '/{id}', name: 'show', defaults: ['titulo' => 'Datos del Participante'], methods: ['GET'])]
    public function show(ParticipanteEdicion $participanteEdicion): Response
    {
        return $this->render('intranet/forpas/gestor/participante_edicion/show.html.twig', [
            'participante_edicion' => $participanteEdicion,
        ]);
    }
    #[Route(path: '/{id}/edit', name: 'edit', defaults: ['titulo' => 'Editar Inscripción del Participante'], methods: ['GET', 'POST'])]
    public function edit(Request $request, ParticipanteEdicion $participanteEdicion, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ParticipanteEdicionType::class, $participanteEdicion);
        $form->handleRequest($request);
        $edicionId = $participanteEdicion->getEdicion()->getId();

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Datos modificados satisfactoriamente.');
            return $this->redirectToRoute('intranet_forpas_gestor_participante_edicion_index', ['edicionId' => $edicionId], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intranet/forpas/gestor/participante_edicion/edit.html.twig', [
            'participante_edicion' => $participanteEdicion,
            'form' => $form,
            'edicionId' => $edicionId,
        ]);
    }
    #[Route(path: '/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, ParticipanteEdicion $participanteEdicion, EntityManagerInterface $entityManager): Response
    {
        $fechaActual = new DateTime();
        $fechaInicioEdicion = $participanteEdicion->getEdicion()->getFechaInicio();

        $edicionId = $participanteEdicion->getEdicion()->getId();

        if ($fechaInicioEdicion <= $fechaActual) {
            $this->addFlash('warning', 'No se puede eliminar a un participante de una edición que ya ha comenzado. Use baja justificada');
        } else {

            if ($this->isCsrfTokenValid('delete' . $participanteEdicion->getId(), $request->getPayload()->getString('_token'))) {
                // Eliminamos la inscripción de las colecciones de Participante y Edición
                $participante = $participanteEdicion->getParticipante();
                $edicion = $participanteEdicion->getEdicion();
                $participante?->removeParticipanteEdiciones($participanteEdicion);
                $edicion?->removeParticipantesEdicion($participanteEdicion);
                $entityManager->flush();
                $this->addFlash('success', 'Participante eliminado correctamente.');
            }
        }

        return $this->redirectToRoute('intranet_forpas_gestor_participante_edicion_index', ['edicionId' => $edicionId], Response::HTTP_SEE_OTHER);
    }
}
