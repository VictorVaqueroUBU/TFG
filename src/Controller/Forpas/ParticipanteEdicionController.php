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

/**
 * Controlador para gestionar las inscripciones de los participantes del PTGAS.
 * @author Víctor M. Vaquero <vvm1002@alu.ubu.es>
 */
#[Route(path: '/intranet/forpas/gestor/participante_edicion', name: 'intranet_forpas_gestor_participante_edicion_')]
final class ParticipanteEdicionController extends AbstractController
{
    #[Route(path: '/new/{id}/{edicionId}', name: 'new', defaults: ['titulo' => 'Crear Nueva Inscripción'], methods: ['GET', 'POST'])]
    public function new(int $id, int $edicionId, Request $request, EntityManagerInterface $entityManager,
                        ParticipanteRepository $participanteRepository, EdicionRepository $edicionRepository): Response
    {
        // Obtenemos el participante y la edición
        $participante = $participanteRepository->find($id);
        $edicion = $edicionRepository->find($edicionId);

        $participanteEdicion = new ParticipanteEdicion();
        $participanteEdicion->setParticipante($participante);
        $participanteEdicion->setEdicion($edicion);
        $participanteEdicion->setFechaSolicitud(new \DateTime());

        $entityManager->persist($participanteEdicion);
        $entityManager->flush();
        $this->addFlash('success', 'Inscripción realizada satisfactoriamente.');
        return $this->redirectToRoute('intranet_forpas_gestor_participante_edicion_index', ['edicionId' => $edicionId], Response::HTTP_SEE_OTHER);
    }

}
