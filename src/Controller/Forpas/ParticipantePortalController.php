<?php

namespace App\Controller\Forpas;

use App\Form\Forpas\ParticipanteContactoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controlador para gestionar el Portal del Participante.
 * @author Víctor M. Vaquero <vvm1002@alu.ubu.es>
 */
#[Route(path: '/intranet/forpas/participante', name: 'intranet_forpas_participante_')]
class ParticipantePortalController extends AbstractController
{
    #[Route(path: '/mis-datos', name: 'mis_datos', defaults: ['titulo' => 'Mis datos de contacto'], methods: ['GET', 'POST'])]
    public function misDatos(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        // Verificamos que el usuario tiene un participante asociado
        if (!$user || !$user->getParticipante()) {
            throw $this->createAccessDeniedException('No tienes acceso a esta sección.');
        }

        $participante = $user->getParticipante();
        $form = $this->createForm(ParticipanteContactoType::class, $participante,
            ['email' => $participante->getUsuario()->getEmail()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Tus datos de contacto se han actualizado correctamente.');
            return $this->redirectToRoute('intranet_forpas_participante', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intranet/forpas/participante/edit.html.twig', [
            'participante' => $user->getParticipante(),
            'form' => $form,
        ]);
    }
}
