<?php

namespace App\Controller\Forpas;

use App\Entity\Forpas\Asistencia;
use App\Entity\Forpas\Edicion;
use App\Entity\Forpas\FormadorEdicion;
use App\Entity\Forpas\ParticipanteEdicion;
use App\Entity\Forpas\Sesion;
use App\Entity\Sistema\Usuario;
use App\Form\Forpas\AsistenciaType;
use App\Form\Forpas\CalificacionType;
use App\Form\Forpas\FormadorContactoType;
use App\Form\Forpas\SesionType;
use App\Repository\Forpas\FormadorEdicionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/**
 * Controlador para gestionar el Portal del Formador.
 * @author Víctor M. Vaquero <vvm1002@alu.ubu.es>
 */
#[Route(path: '/intranet/forpas/formador', name: 'intranet_forpas_formador_')]
class FormadorPortalController extends AbstractController
{
    private function getUsuarioFormador(): Usuario
    {
        /** @var Usuario|null $user */
        $user = $this->getUser();

        if (!$user || !$user->getFormador()) {
            throw $this->createAccessDeniedException('No tienes acceso a esta sección.');
        }

        return $user;
    }
    private function getEdicionAsignada(int $id, EntityManagerInterface $entityManager): Edicion
    {
        $user = $this->getUsuarioFormador();

        $edicion = $entityManager->getRepository(Edicion::class)->find($id);
        if (!$edicion) {
            throw $this->createNotFoundException('No se ha encontrado la edición solicitada.');
        }

        $asignacion = $entityManager->getRepository(FormadorEdicion::class)->findOneBy([
            'formador' => $user->getFormador()->getId(),
            'edicion' => $id
        ]);

        if (!$asignacion) {
            throw $this->createAccessDeniedException('No tiene acceso a esta edición.');
        }

        return $edicion;
    }
    #[Route(path: '/mis-datos', name: 'mis_datos', defaults: ['titulo' => 'Mis datos de contacto'], methods: ['GET', 'POST'])]
    public function misDatos(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUsuarioFormador();

        $formador = $user->getFormador();
        $form = $this->createForm(FormadorContactoType::class, $formador,
            ['email' => $formador->getUsuario()->getEmail()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Tus datos de contacto se han actualizado correctamente.');
            return $this->redirectToRoute('intranet_forpas_formador', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intranet/forpas/formador/datos_edit.html.twig', [
            'formador' => $user->getFormador(),
            'form' => $form,
            'edicion' => $edicion ?? null,
        ]);
    }

    #[Route(path: '/mis-ediciones', name: 'mis_ediciones', defaults: ['titulo' => 'Ediciones Asignadas'], methods: ['GET'])]
    public function ediciones(FormadorEdicionRepository $formadorEdicionRepository): Response
    {
        $user = $this->getUsuarioFormador();
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
        $edicion = $this->getEdicionAsignada($id, $entityManager);
        $sesionStats = $entityManager->getRepository(Sesion::class)
            ->calcularSesionesYHoras($edicion);
        $sessionsAsistencias = $entityManager->getRepository(Asistencia::class)
            ->contarAsistenciasPorSesion($edicion);
        $sessionJustifica = $entityManager->getRepository(Asistencia::class)
            ->contarJustificacionesPorSesion($edicion);
        $calificacionesStats = $entityManager->getRepository(ParticipanteEdicion::class)
            ->contarCalificaciones($edicion);

        $calificacionesCompletas =
            $calificacionesStats['aptos'] > 0 ||
            $calificacionesStats['noAptos'] > 0 ||
            $calificacionesStats['noPresentados'] > 0;

        return $this->render('intranet/forpas/formador/edicion_show.html.twig', [
            'edicion' => $edicion,
            'sesionesGrabadas' => $sesionStats['sesionesGrabadas'],
            'horasGrabadas' => $sesionStats['horasGrabadas'],
            'horasVirtualesGrabadas' => $sesionStats['horasVirtualesGrabadas'],
            'sessionsAsistencias' => $sessionsAsistencias,
            'sessionJustifica' => $sessionJustifica,
            'calificaciones' => $calificacionesStats,
            'calificacionesCompletas' => $calificacionesCompletas,
        ]);
    }
    #[Route('/mis-ediciones/{id}/remitir', name: 'mis_ediciones_remitir', methods: ['POST'])]
    public function remitirDatos(int $id, EntityManagerInterface $entityManager): Response
    {
        $edicion = $this->getEdicionAsignada($id, $entityManager);
        // Actualizamos el estado de la edición a 1
        $edicion->setEstado(1);
        $entityManager->persist($edicion);
        $entityManager->flush();

        $this->addFlash('success', 'Los datos de la edición han sido remitidos correctamente.');
        return $this->redirectToRoute('intranet_forpas_formador_mis_ediciones');
    }

    #[Route(path: '/sesion-new/{edicionId}', name: 'sesion_new', defaults: ['titulo' => 'Crear Nueva Sesión'], methods: ['GET', 'POST'])]
    public function sesionNew(Request $request, int $edicionId, EntityManagerInterface $entityManager): Response
    {
        $edicion = $this->getEdicionAsignada($edicionId, $entityManager);
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
        $nuevaSesion->setFormador($this->getUser()->getFormador());
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
            $this->getUser()->getFormador()->addSesion($nuevaSesion);
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
    #[Route(path: '/sesion-edit/{id}', name: 'sesion_edit', defaults: ['titulo' => 'Editar Sesión'], methods: ['GET', 'POST'])]
    public function sesionEdit(Request $request, Sesion $sesion, EntityManagerInterface $entityManager): Response
    {
        $edicion = $this->getEdicionAsignada($sesion->getEdicion()->getId(), $entityManager);
        $form = $this->createForm(SesionType::class, $sesion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Los datos de la sesión se han modificado satisfactoriamente.');
            return $this->redirectToRoute('intranet_forpas_formador_mis_ediciones_show', ['id' => $edicion->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intranet/forpas/formador/sesion_edit.html.twig', [
            'sesion' => $sesion,
            'form' => $form,
            'edicion' => $edicion
        ]);
    }
    #[Route(path: '/{id}', name: 'sesion_delete', methods: ['POST'])]
    public function SesionDelete(Request $request, Sesion $sesion, EntityManagerInterface $entityManager): Response
    {
        $edicion = $this->getEdicionAsignada($sesion->getEdicion()->getId(), $entityManager);
        if ($this->isCsrfTokenValid('delete'.$sesion->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sesion);
            $entityManager->flush();
            $this->addFlash('success', 'Sesion eliminada correctamente.');
        }

        return $this->redirectToRoute('intranet_forpas_formador_mis_ediciones_show', ['id' => $edicion->getId()], Response::HTTP_SEE_OTHER);
    }
    #[Route('/sesion/{id}/asistencia', name: 'sesion_fillIn', defaults: ['titulo' => 'Introducir Asistencia'], methods: ['GET', 'POST'])]
    public function fillInAsistencia(Request $request, Sesion $sesion, EntityManagerInterface $entityManager): Response {
        $edicion = $this->getEdicionAsignada($sesion->getEdicion()->getId(), $entityManager);
        // Obtenemos los participantes inscritos en la edición de esta sesión
        $participantesEdicion = $edicion->getParticipantesEdicion();

        $asistencias = [];
        foreach ($participantesEdicion as $participanteEdicion) {
            // Intentamos recuperar la asistencia existente
            $asistencia = $entityManager->getRepository(Asistencia::class)
                ->findOneBy(['sesion' => $sesion, 'participante' => $participanteEdicion->getParticipante()]);

            if (!$asistencia) {
                $asistencia = new Asistencia();
                $asistencia->setSesion($sesion);
                $asistencia->setParticipante($participanteEdicion->getParticipante());
                $asistencia->setFormador($this->getUser()->getFormador());
            }

            $asistencias[] = $asistencia;
        }

        $form = $this->createFormBuilder(['asistencias' => $asistencias])
            ->add('asistencias', CollectionType::class, [
                'entry_type' => AsistenciaType::class,
                'allow_add' => false,
                'allow_delete' => false,
                'disabled' => $edicion->getEstado() != 0,
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($form->getData()['asistencias'] as $asistencia) {
                $entityManager->persist($asistencia);
            }
            $entityManager->flush();

            $this->addFlash('success', 'Asistencias registradas correctamente.');
            return $this->redirectToRoute('intranet_forpas_formador_mis_ediciones_show', [
                'id' => $edicion->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intranet/forpas/formador/asistencia_fillIn.html.twig', [
            'form' => $form->createView(),
            'sesion' => $sesion,
            'participantesEdicion' => $participantesEdicion,
        ]);
    }
    #[Route('/edicion/{id}/calificaciones', name: 'calificaciones', defaults: ['titulo' => 'Introducir Calificaciones'], methods: ['GET', 'POST'])]
    public function registrarCalificaciones(Request $request, Edicion $edicion, EntityManagerInterface $entityManager): Response {
        $edicion = $this->getEdicionAsignada($edicion->getId(), $entityManager);

        // Verificamos si la edición es calificable
        if (!$edicion->getCurso()->isCalificable()) {
            throw $this->createAccessDeniedException('Esta edición no es calificable.');
        }

        // Obtenemos los registros de ParticipanteEdicion
        $participantesEdicion = $edicion->getParticipantesEdicion();

        $formBuilder = $this->createFormBuilder();
        foreach ($participantesEdicion as $i => $participanteEdicion) {
            $formBuilder->add("calificaciones_$i", CalificacionType ::class, [
                'data' => [
                    'apto' => $participanteEdicion->getApto(),
                    'pruebaFinal' => $participanteEdicion->getPruebaFinal(),
                ],
                'disabled' => $edicion->getEstado() != 0
            ]);
        }
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($participantesEdicion as $i => $participanteEdicion) {
                $data = $form->get("calificaciones_$i")->getData();
                $participanteEdicion->setApto($data['apto']);
                $participanteEdicion->setPruebaFinal($data['pruebaFinal']);
                $entityManager->persist($participanteEdicion);
            }
            $entityManager->flush();

            $this->addFlash('success', 'Calificaciones registradas correctamente.');
            return $this->redirectToRoute('intranet_forpas_formador_mis_ediciones_show', [
                'id' => $edicion->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intranet/forpas/formador/calificaciones.html.twig', [
            'form' => $form->createView(),
            'edicion' => $edicion,
            'participantesEdicion' => $participantesEdicion,
        ]);
    }
}