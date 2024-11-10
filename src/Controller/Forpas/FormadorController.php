<?php

namespace App\Controller\Forpas;

use App\Entity\Forpas\Formador;
use App\Form\Forpas\FormadorType;
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
}
