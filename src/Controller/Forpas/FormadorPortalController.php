<?php

namespace App\Controller\Forpas;

use App\Entity\Sistema\Usuario;
use App\Form\Forpas\FormadorContactoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controlador para gestionar el Portal del Formador.
 * @author Víctor M. Vaquero <vvm1002@alu.ubu.es>
 */
#[Route(path: '/intranet/forpas/formador', name: 'intranet_forpas_formador_')]
class FormadorPortalController extends AbstractController
{
    #[Route(path: '/mis-datos', name: 'mis_datos', defaults: ['titulo' => 'Mis datos de contacto'], methods: ['GET', 'POST'])]
    public function misDatos(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var Usuario|null $user */
        $user = $this->getUser();
        // Verificamos que el usuario tiene un formador asociado
        if (!$user || !$user->getFormador()) {
            throw $this->createAccessDeniedException('No tienes acceso a esta sección.');
        }

        $formador = $user->getFormador();
        $form = $this->createForm(FormadorContactoType::class, $formador,
            ['email' => $formador->getUsuario()->getEmail()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Tus datos de contacto se han actualizado correctamente.');
            return $this->redirectToRoute('intranet_forpas_formador', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intranet/forpas/formador/edit.html.twig', [
            'formador' => $user->getFormador(),
            'form' => $form,
        ]);
    }
}
