<?php

namespace App\Controller\Forpas;

use App\Entity\Forpas\ParticipanteEdicion;
use App\Form\Forpas\ParticipanteEdicionType;
use App\Repository\Forpas\AsistenciaRepository;
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
    #[Route(path: '/edicion/{edicionId}/certificar', name: 'certificar', defaults: ['titulo' => 'Certificar Edición'], methods: ['GET'])]
    public function certificar(
        int $edicionId,
        ParticipanteEdicionRepository $participanteEdicionRepository,
        EdicionRepository $edicionRepository,
        AsistenciaRepository $asistenciaRepository
    ): Response {
        $edicion = $edicionRepository->find($edicionId);
        $inscripciones = $participanteEdicionRepository->findBy(['edicion' => $edicionId]);
        $asistencias = $asistenciaRepository->findAllByEdicion($edicionId);
        $datos = [];

        // Inicializamos el array con los datos básicos de cada participante
        foreach ($inscripciones as $pe) {
            $datos[$pe->getParticipante()->getId()] = [
                'nif' => $pe->getParticipante()->getNif(),
                'apellidos' => $pe->getParticipante()->getApellidos(),
                'nombre' => $pe->getParticipante()->getNombre(),
                'apto' => $pe->getApto(),
                'pruebaFinal' => $pe->getPruebaFinal(),
                'bajaJustificada' => $pe->getBajaJustificada(),
                'certificado' => $pe->getCertificado(),
                'libro' => $pe->getLibro(),
                'numeroTitulo' => $pe->getNumeroTitulo(),
                'dias' => 0,
                'minutosAsistencia' => 0,
                'asistenciasFechas' => [],
                'justificacionesFechas' => [],
            ];
        }

        // Rellenamos las fechas de asistencias y justificaciones
        foreach ($asistencias as $asistencia) {
            $pId = $asistencia->getParticipante()->getId();
            $estado = $asistencia->getEstado(); // 'asiste', 'justifica', 'ninguno'
            $fecha = $asistencia->getSesion()->getFecha(); // Obtenemos la fecha de la sesión
            $duracion = $asistencia->getSesion()->getDuracion();

            if ($estado === 'asiste') {
                $datos[$pId]['asistenciasFechas'][] = $fecha;
                $datos[$pId]['dias']++;
                $datos[$pId]['minutosAsistencia'] += $duracion;
            } elseif ($estado === 'justifica') {
                $datos[$pId]['justificacionesFechas'][] = $fecha;
            }
        }
        return $this->render('intranet/forpas/gestor/participante_edicion/certificar.html.twig', [
            'edicion' => $edicion,
            'datos_participantes' => $datos,
        ]);
    }
    #[Route(path: '/edicion/{edicionId}/certificar/procesar', name: 'certificar_procesar', methods: ['POST'])]
    public function calcularCertificados(
        int $edicionId,
        Request $request,
        EntityManagerInterface $entityManager,
        EdicionRepository $edicionRepository
    ): Response {
        $edicion = $edicionRepository->find($edicionId);
        if (!$edicion) {
            throw $this->createNotFoundException('Edición no encontrada.');
        }

        // Recuperamos el array de datos desde el formulario
        $datos = json_decode($request->request->get('datos_participantes'), true);
        $anyoCurso = '20' . substr($edicion->getCurso()->getCodigoCurso(), 0, 2);
        $ultimoTitulo = $entityManager->getRepository(ParticipanteEdicion::class)->findMaxNumeroTituloByLibro($anyoCurso) ?? 0;
        $porcentajeAsistencia = 75;

        // Procesamos cada participante
        foreach ($datos as $pId => $participante) {
            $cumpleAsistencia = $participante['minutosAsistencia'] >= $edicion->getCurso()->getHoras() * 60 * ($porcentajeAsistencia / 100);
            $cumpleApto = !$edicion->getCurso()->isCalificable() || $participante['apto'] === 1;
            $participanteEdicion = $entityManager->getRepository(ParticipanteEdicion::class)
                ->findOneBy([
                    'participante' => $pId,
                    'edicion' => $edicionId
                ]);

            if ($participante['certificado'] != 'S'){
                if ($participanteEdicion && $participante['bajaJustificada'] === null && $cumpleAsistencia && $cumpleApto) {
                    $participanteEdicion->setCertificado('S');
                    $participanteEdicion->setLibro($anyoCurso);
                    $participanteEdicion->setNumeroTitulo(++$ultimoTitulo);
                } else {
                    $participanteEdicion->setCertificado('N');
                }

                $entityManager->persist($participanteEdicion);
            }
        }

        $edicion->setEstado('2');
        $entityManager->flush();
        $this->addFlash('success', 'Edición certificada correctamente.');
        return $this->redirectToRoute('intranet_forpas_gestor_participante_edicion_certificar', ['edicionId' => $edicionId]);
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
