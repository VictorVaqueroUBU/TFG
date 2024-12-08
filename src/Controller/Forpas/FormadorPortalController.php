<?php

namespace App\Controller\Forpas;

use App\Entity\Forpas\Edicion;
use App\Entity\Forpas\FormadorEdicion;
use App\Entity\Forpas\Sesion;
use App\Entity\Sistema\Usuario;
use App\Form\Forpas\FormadorContactoType;
use App\Form\Forpas\SesionType;
use App\Repository\Forpas\EdicionRepository;
use App\Repository\Forpas\FormadorEdicionRepository;
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

    #[Route(path: '/mis-ediciones', name: 'mis_ediciones', defaults: ['titulo' => 'Mis Ediciones Asignadas'], methods: ['GET'])]
    public function ediciones(FormadorEdicionRepository $formadorEdicionRepository): Response
    {
        /** @var Usuario|null $user */
        $user = $this->getUser();

        // Verificamos que el usuario tiene un formador asociado
        if (!$user || !$user->getFormador()) {
            throw $this->createAccessDeniedException('No tienes acceso a esta sección.');
        }

        $formadorId = $user->getFormador()->getId();

        // Obtenemos las ediciones abiertas y cerradas
        $edicionesAbiertas = $formadorEdicionRepository->findEdicionesAbiertasByFormador($formadorId);
        $edicionesCerradas = $formadorEdicionRepository->findEdicionesCerradasByFormador($formadorId);

        return $this->render('intranet/forpas/formador/mis_ediciones.html.twig', [
            'edicionesAbiertas' => $edicionesAbiertas,
            'edicionesCerradas' => $edicionesCerradas,
        ]);
    }

    #[Route(path: '/mis-ediciones/{id}', name: 'mis_ediciones_show', defaults: ['titulo' => 'Datos de la Edición'], methods: ['GET', 'POST'])]
    public function edicionShow(int $id, EntityManagerInterface $entityManager): Response
    {
        /** @var Usuario|null $user */
        $user = $this->getUser();

        // Verificamos que el usuario tiene un formador asociado
        if (!$user || !$user->getFormador()) {
            throw $this->createAccessDeniedException('No tienes acceso a esta sección.');
        }

        // Obtenemos la edición
        $edicion = $entityManager->getRepository(Edicion::class)->find($id);
        if (!$edicion) {
            throw $this->createNotFoundException('No se ha encontrado la edición solicitada.');
        }

        // Validamos que la edición esté asignada al formador que ha iniciado sesión
        $asignacion = $entityManager->getRepository(FormadorEdicion::class)->findOneBy([
            'formador' => $user->getFormador()->getId(),
            'edicion' => $id
        ]);

        if (!$asignacion) {
            throw $this->createAccessDeniedException('No tiene acceso a esta edición.');
        }

        // Obtenemos los datos de las sesiones para usarlo en la plantilla
        $sesiones = $edicion->getSesionesEdicion();
        $sesionesGrabadas = count($sesiones);
        $horasGrabadas = 0;
        $horasVirtualesGrabadas = 0;
        foreach ($sesiones as $sesion) {
            $horasGrabadas += $sesion->getDuracion();
            if ($sesion->getTipo() === 1) {
                $horasVirtualesGrabadas += $sesion->getDuracion();
            }

        }

        return $this->render('intranet/forpas/formador/edicion_show.html.twig', [
            'edicion' => $edicion,
            'sesionesGrabadas' => $sesionesGrabadas,
            'horasGrabadas' => $horasGrabadas,
            'horasVirtualesGrabadas' => $horasVirtualesGrabadas,
        ]);
    }
    #[Route(path: '/sesion-new/{edicionId}', name: 'sesion_new', defaults: ['titulo' => 'Crear Nueva Sesión'], methods: ['GET', 'POST'])]
    public function sesionNew(Request $request, int $edicionId, EntityManagerInterface $entityManager, EdicionRepository $edicionRepository): Response
    {
        /** @var Usuario|null $user */
        $user = $this->getUser();

        // Verificamos que el usuario tiene un formador asociado
        if (!$user || !$user->getFormador()) {
            throw $this->createAccessDeniedException('No tienes acceso a esta sección.');
        }

        $edicion = $edicionRepository->find($edicionId);

        // Validamos que la edición esté asignada al formador que ha iniciado sesión
        $asignacion = $entityManager->getRepository(FormadorEdicion::class)->findOneBy([
            'formador' => $user->getFormador()->getId(),
            'edicion' => $edicion
        ]);

        if (!$asignacion) {
            throw $this->createAccessDeniedException('No tiene acceso a esta edición.');
        }

        // Comprobamos que aún queden sesiones por crear
        if (count($edicion->getSesionesEdicion()) >= $edicion->getSesiones()) {
            $this->addFlash('warning', 'Ya se alcanzó el número máximo de sesiones para esta edición.');
            return $this->redirectToRoute('intranet_forpas_formador_mis_ediciones_show', ['id' => $edicionId]);
        }

        // Obtenemos la duración ya grabada
        $sesiones = $edicion->getSesionesEdicion();
        $horasGrabadas = 0;
        $horasVirtualesGrabadas = 0;
        foreach ($sesiones as $sesion) {
            $horasGrabadas += $sesion->getDuracion();
            if ($sesion->getTipo() === 1) {
                $horasVirtualesGrabadas += $sesion->getDuracion();
            }

        }

        $nuevaSesion = new Sesion();
        $nuevaSesion->setEdicion($edicion);
        $nuevaSesion->setFormador($user->getFormador());
        $form = $this->createForm(SesionType::class, $nuevaSesion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (($horasGrabadas + $nuevaSesion->getDuracion()) > $edicion->getCurso()->getHoras() * 60) {
                // Se exceden las horas totales
                $this->addFlash('warning', 'Sesión no grabada. Con los datos introducidos se superan las horas totales del curso.');
                return $this->redirectToRoute('intranet_forpas_formador_mis_ediciones_show', ['id' => $edicionId]);
            }
            if ($nuevaSesion->getTipo() === 1 && ($horasVirtualesGrabadas + $nuevaSesion->getDuracion()) > $edicion->getCurso()->getHorasVirtuales() * 60) {
                // Se exceden las horas virtuales
                $this->addFlash('warning', 'Sesión no grabada. Con los datos introducidos se superan las horas virtuales totales del curso.');
                return $this->redirectToRoute('intranet_forpas_formador_mis_ediciones_show', ['id' => $edicionId]);
            }
            $edicion->addSesionesEdicion($nuevaSesion);
            $user->getFormador()->addSesion($nuevaSesion);
            $entityManager->persist($nuevaSesion);
            $entityManager->flush();
            $this->addFlash('success', 'La sesión se ha creado correctamente.');
            return $this->redirectToRoute('intranet_forpas_formador_mis_ediciones_show', ['id' => $edicionId], Response::HTTP_SEE_OTHER);
        }
        return $this->render('intranet/forpas/formador/sesion_new.html.twig', [
            'nuevaSesion' => $nuevaSesion,
            'form' => $form,
            'edicion' => $edicion
        ]);
    }
    #[Route(path: '/{id}/sesion-edit', name: 'sesion_edit', defaults: ['titulo' => 'Editar Sesión'], methods: ['GET', 'POST'])]
    public function sesionEdit(Request $request, Sesion $sesion, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SesionType::class, $sesion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Los datos de la sesión se han modificado satisfactoriamente.');
            return $this->redirectToRoute('intranet_forpas_formador_mis_ediciones_show', ['id' => $sesion->getEdicion()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intranet/forpas/formador/sesion_edit.html.twig', [
            'sesion' => $sesion,
            'form' => $form,
            'edicion' => $sesion->getEdicion()
        ]);
    }
    #[Route(path: '/{id}', name: 'sesion_delete', methods: ['POST'])]
    public function SesionDelete(Request $request, Sesion $sesion, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sesion->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sesion);
            $entityManager->flush();
            $this->addFlash('success', 'Sesion eliminada correctamente.');
        }

        return $this->redirectToRoute('intranet_forpas_formador_mis_ediciones_show', ['id' => $sesion->getEdicion()->getId()], Response::HTTP_SEE_OTHER);
    }
}