<?php

namespace App\Controller\Forpas;

use App\Entity\Forpas\Formador;
use App\Entity\Forpas\Participante;
use App\Form\Forpas\ParticipanteType;
use App\Repository\Forpas\EdicionRepository;
use App\Repository\Forpas\FormadorRepository;
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
    #[Route(path: '/find', name: 'find', defaults: ['titulo' => 'Listado de Participantes'], methods: ['GET'])]
    public function find(ParticipanteRepository $participanteRepository): Response
    {
        return $this->render('intranet/forpas/gestor/participante/find.html.twig', [
            'participantes' => $participanteRepository->findAll(),
        ]);
    }
    #[Route(path: '/new/{id}', name: 'new', defaults: ['titulo' => 'Crear Participante'], methods: ['GET', 'POST'])]
    public function new(
        int $id,
        FormadorRepository $formadorRepository,
        ParticipanteRepository $participanteRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Cargamos el formador por ID
        $formador = $formadorRepository->find($id);

        // Verificamos si ya existe un participante asociado a este usuario
        $usuario = $formador->getUsuario();
        if ($participanteRepository->findOneBy(['usuario' => $usuario])) {
            $this->addFlash('warning', 'Este usuario ya tiene un perfil de participante.');
            return $this->redirectToRoute('intranet_forpas_gestor_formador_find');
        }

        // Creamos el nuevo participante con los datos del formador
        $participante = new Participante();
        $participante->setNif($formador->getNif());
        $participante->setNombre($formador->getNombre());
        $participante->setApellidos($formador->getApellidos());
        $participante->setOrganizacion($formador->getOrganizacion());
        $participante->setUnidad('Nota: !Unidad == cesado. No permite inscripción');
        $participante->setUsuario($usuario);

        // Actualizamos los roles del usuario para incluir ROLE_USER
        $roles = $usuario->getRoles();
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }
        $usuario->setRoles($roles);

        // Relación bidireccional
        $usuario->setParticipante($participante);

        // Persistimos el nuevo participante y los cambios del usuario
        $entityManager->persist($participante);
        $entityManager->flush();

        $this->addFlash('success', 'El alta del participante se ha realizado satisfactoriamente.');
        return $this->redirectToRoute('intranet_forpas_gestor_participante_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route(path: '/append/{id}', name: 'append', defaults: ['titulo' => 'Añadir Participante'], methods: ['GET'])]
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
    #[Route(path: '/{id}/edit', name: 'edit', defaults: ['titulo' => 'Editar Participante'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Participante $participante, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ParticipanteType::class, $participante);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Los datos del participante se han modificado satisfactoriamente.');
            return $this->redirectToRoute('intranet_forpas_gestor_participante_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intranet/forpas/gestor/participante/edit.html.twig', [
            'participante' => $participante,
            'form' => $form,
        ]);
    }
    #[Route(path: '/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Participante $participante, EntityManagerInterface $entityManager): Response
    {
        // Verificamos si el participante tiene ediciones asociadas
        if (!$participante->getParticipanteEdiciones()->isEmpty()) {
            // Si tiene ediciones, redirige con un mensaje de error
            $this->addFlash('warning', 'No se puede eliminar al participante porque tiene ediciones asociadas.');
            return $this->redirectToRoute('intranet_forpas_gestor_participante_index');
        }

        if ($this->isCsrfTokenValid('delete'.$participante->getId(), $request->getPayload()->getString('_token'))) {
            $usuario = $participante->getUsuario();
            $formador = $entityManager->getRepository(Formador::class)->findOneBy(['usuario' => $usuario]);
            $entityManager->remove($participante);

            if ($formador) {
                $roles = $usuario->getRoles();
                $roles = array_filter($roles, fn($role) => $role !== 'ROLE_USER');
                $roles = array_values($roles);
                $usuario->setRoles($roles);
            }
            else {
                $entityManager->remove($usuario);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Participante eliminado correctamente.');
        }

        return $this->redirectToRoute('intranet_forpas_gestor_participante_index', [], Response::HTTP_SEE_OTHER);
    }
}
