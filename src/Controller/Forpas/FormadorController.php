<?php

namespace App\Controller\Forpas;

use App\Entity\Forpas\Formador;
use App\Entity\Forpas\Participante;
use App\Form\Forpas\FormadorType;
use App\Repository\Forpas\EdicionRepository;
use App\Repository\Forpas\FormadorEdicionRepository;
use App\Repository\Forpas\FormadorRepository;
use App\Repository\Forpas\ParticipanteRepository;
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
    #[Route(path: '/find', name: 'find', defaults: ['titulo' => 'Listado de Formadores'], methods: ['GET'])]
    public function find(FormadorRepository $formadorRepository): Response
    {
        return $this->render('intranet/forpas/gestor/formador/find.html.twig', [
            'formadores' => $formadorRepository->findAll(),
        ]);
    }
    #[Route(path: '/new/{id}', name: 'new', defaults: ['titulo' => 'Crear Formador'], methods: ['GET', 'POST'])]
    public function new(
        int $id,
        FormadorRepository $formadorRepository,
        ParticipanteRepository $participanteRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Cargamos el participante por ID
        $participante = $participanteRepository->find($id);

        // Verificamos si ya existe un formador asociado a este usuario
        $usuario = $participante->getUsuario();
        if ($formadorRepository->findOneBy(['usuario' => $usuario])) {
            $this->addFlash('warning', 'Este usuario ya tiene un perfil de formador.');
            return $this->redirectToRoute('intranet_forpas_gestor_participante_find');
        }

        // Creamos el nuevo formador con los datos del participante
        $formador = new Formador();
        $formador->setNif($participante->getNif());
        $formador->setNombre($participante->getNombre());
        $formador->setApellidos($participante->getApellidos());
        $formador->setOrganizacion($participante->getOrganizacion());
        $formador->setUsuario($usuario);

        // Actualizamos los roles del usuario para incluir ROLE_TEACHER
        $roles = $usuario->getRoles();
        if (!in_array('ROLE_TEACHER', $roles)) {
            $roles[] = 'ROLE_TEACHER';
        }
        $usuario->setRoles($roles);

        // Relación bidireccional
        $usuario->setFormador($formador);

        // Persistimos el nuevo formador y los cambios del usuario
        $entityManager->persist($formador);
        $entityManager->flush();

        $this->addFlash('success', 'El alta del formador se ha realizado satisfactoriamente.');
        return $this->redirectToRoute('intranet_forpas_gestor_formador_index', [], Response::HTTP_SEE_OTHER);
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
            $this->addFlash('warning', 'No se puede eliminar al formador porque tiene ediciones asociadas.');
            return $this->redirectToRoute('intranet_forpas_gestor_formador_index');
        }

        if ($this->isCsrfTokenValid('delete'.$formador->getId(), $request->getPayload()->getString('_token'))) {
            $usuario = $formador->getUsuario();
            $participante = $entityManager->getRepository(Participante::class)->findOneBy(['usuario' => $usuario]);
            $entityManager->remove($formador);

            if ($participante) {
                $roles = $usuario->getRoles();
                $roles = array_filter($roles, fn($role) => $role !== 'ROLE_TEACHER');
                $roles = array_values($roles);
                $usuario->setRoles($roles);
            } else {
                $entityManager->remove($usuario);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Formador eliminado correctamente.');
        }

        return $this->redirectToRoute('intranet_forpas_gestor_formador_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route(path: '/{id}/mis-ediciones', name: 'mis_ediciones', defaults: ['titulo' => 'Ediciones asignadas'], methods: ['GET'])]
    public function misEdiciones(Formador $formador, FormadorEdicionRepository $formadorEdicionRepository): Response {
        // Obtenemos las ediciones abiertas y cerradas asignadas al formador
        $edicionesAbiertas = $formadorEdicionRepository->findEdicionesAbiertasByFormador($formador->getId());
        $edicionesCerradas = $formadorEdicionRepository->findEdicionesCerradasByFormador($formador->getId());

        return $this->render('intranet/forpas/gestor/formador/mis_ediciones.html.twig', [
            'edicionesAbiertas' => $edicionesAbiertas,
            'edicionesCerradas' => $edicionesCerradas,
            'formador' => $formador,
        ]);
    }
}
