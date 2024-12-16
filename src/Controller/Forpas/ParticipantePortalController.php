<?php

namespace App\Controller\Forpas;

use App\Entity\Forpas\Curso;
use App\Entity\Forpas\Edicion;
use App\Entity\Forpas\ParticipanteEdicion;
use App\Entity\Sistema\Usuario;
use App\Form\Forpas\ParticipanteContactoType;
use App\Repository\Forpas\CursoRepository;
use App\Repository\Forpas\ParticipanteEdicionRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
        /** @var Usuario|null $user */
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
    #[Route(path: '/ficha-formativa', name: 'ficha_formativa', defaults: ['titulo' => 'Ficha Formativa Personal'], methods: ['GET'])]
    public function fichaFormativa(EntityManagerInterface $entityManager): Response
    {
        /** @var Usuario|null $user */
        $user = $this->getUser();

        // Verificamos si el usuario tiene un participante asociado
        if (!$user || !$user->getParticipante()) {
            throw $this->createAccessDeniedException('No tienes acceso a esta sección.');
        }

        $participante = $user->getParticipante();
        // Consultamos las ediciones por categoría
        /** @var ParticipanteEdicionRepository $repository */
        $repository = $entityManager->getRepository(ParticipanteEdicion::class);
        $proximasEdiciones = $repository->findProximasEdiciones($participante);
        $edicionesCertificadas = $repository->findEdicionesCertificadas($participante);
        $otrasEdiciones = $repository->findOtrasEdiciones($participante);

        return $this->render('intranet/forpas/participante/ficha_formativa.html.twig', [
            'proximasEdiciones' => $proximasEdiciones,
            'edicionesCertificadas' => $edicionesCertificadas,
            'otrasEdiciones' => $otrasEdiciones,
        ]);
    }

    #[Route(path: '/cursos', name: 'cursos', defaults: ['titulo' => 'Listado de cursos'], methods: ['GET'])]
    public function listarCursos(CursoRepository $cursoRepository): Response
    {
        $year = (int) date('Y'); // Obtiene el año actual
        $cursos = $cursoRepository->findByYear($year);
        return $this->render('intranet/forpas/participante/cursos.html.twig', [
            'cursos' => $cursos,
        ]);
    }

    #[Route(path: '/cursos/{id}', name: 'curso_show', defaults: ['titulo' => 'Datos del curso'], methods: ['GET'])]
    public function showCurso(Curso $curso): Response
    {
        return $this->render('intranet/forpas/participante/curso_show.html.twig', [
            'curso' => $curso,
        ]);
    }

    #[Route(path: '/cursos/{id}/ediciones', name: 'curso_ediciones', defaults: ['titulo' => 'Ediciones del curso'], methods: ['GET'])]
    public function listarEdiciones(Curso $curso, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): Response
    {
        $ediciones = $entityManager->getRepository(Edicion::class)->findEdicionesSinCeroCero($curso);
        /** @var Usuario $user */
        $user = $this->getUser();
        $participante = $user->getParticipante();

        return $this->render('intranet/forpas/participante/ediciones.html.twig', [
            'curso' => $curso,
            'ediciones' => $ediciones,
            'participante' => $participante,
            'urlGenerator' => $urlGenerator,
        ]);
    }

    #[Route('/inscripcion/cancelar/{id}', name: 'inscripcion_cancelar')]
    public function cancelarInscripcion(int $id, EntityManagerInterface $entityManager): Response
    {
        // Obtenemos el participante y la edición
        /** @var Usuario $user */
        $user = $this->getUser();
        $participante = $user->getParticipante();
        $edicion = $entityManager->getRepository(Edicion::class)->find($id);

        // Buscamos la inscripción en ParticipanteEdicion
        $inscripcion = $entityManager->getRepository(ParticipanteEdicion::class)->findOneBy([
            'participante' => $participante,
            'edicion' => $edicion,
        ]);

        if ($inscripcion) {
            // Eliminar referencias bidireccionales
            $participante->removeParticipanteEdiciones($inscripcion);
            $inscripcion->getEdicion()->removeParticipantesEdicion($inscripcion);

            // Eliminar la inscripción
            $entityManager->remove($inscripcion);
            $entityManager->flush();

            $this->addFlash('success', 'Tu inscripción ha sido cancelada correctamente.');
        } else {
            $this->addFlash('warning', 'No estás inscrito en esta edición.');
        }

        return $this->redirectToRoute('intranet_forpas_participante');
    }

    #[Route('/inscripcion/cambiar/{id}', name: 'inscripcion_cambiar')]
    public function cambiarInscripcion(int $id, EntityManagerInterface $entityManager): Response
    {
        // Obtenemos el participante y la edición
        /** @var Usuario $user */
        $user = $this->getUser();
        $participante = $user->getParticipante();
        $edicion = $entityManager->getRepository(Edicion::class)->find($id);

        // Buscamos la inscripción actual
        $inscripcionActual = $participante->getParticipanteEdiciones()->filter(function ($participanteEdicion) use ($edicion) {
            return $participanteEdicion->getEdicion()->getCurso() === $edicion->getCurso();
        })->first();

        if ($inscripcionActual) {
            // Eliminar referencias bidireccionales
            $participante->removeParticipanteEdiciones($inscripcionActual);
            $inscripcionActual->getEdicion()->removeParticipantesEdicion($inscripcionActual);

            // Eliminar la inscripción
            $entityManager->remove($inscripcionActual);
        }

        // Creamos nueva inscripción para la edición actual
        $nuevaInscripcion = new ParticipanteEdicion();
        $nuevaInscripcion->setParticipante($participante);
        $nuevaInscripcion->setEdicion($edicion);
        $nuevaInscripcion->setFechaSolicitud(new DateTime());

        // Añadimos la inscripción a las colecciones
        $participante->addParticipanteEdiciones($nuevaInscripcion);
        $edicion->addParticipantesEdicion($nuevaInscripcion);

        // Persistimos la inscripción
        $entityManager->persist($nuevaInscripcion);
        $entityManager->flush();

        $this->addFlash('success', 'Tu inscripción ha sido cambiada correctamente.');
        return $this->redirectToRoute('intranet_forpas_participante');
    }

    #[Route('/inscripcion/realizar/{id}', name: 'inscripcion_realizar')]
    public function realizarInscripcion(int $id, EntityManagerInterface $entityManager): Response
    {
        // Obtenemos el participante y la edición
        /** @var Usuario $user */
        $user = $this->getUser();
        $participante = $user->getParticipante();
        $edicion = $entityManager->getRepository(Edicion::class)->find($id);

        // Creamos nueva inscripción
        $inscripcion = new ParticipanteEdicion();
        $inscripcion->setParticipante($participante);
        $inscripcion->setEdicion($edicion);
        $inscripcion->setFechaSolicitud(new DateTime());

        // Añadimos la inscripción a las colecciones
        $participante->addParticipanteEdiciones($inscripcion);
        $edicion->addParticipantesEdicion($inscripcion);

        $entityManager->persist($inscripcion);
        $entityManager->flush();

        $this->addFlash('success', 'Tu inscripción ha sido realizada correctamente.');
        return $this->redirectToRoute('intranet_forpas_participante');
    }
    #[Route(path: '/proximas-ediciones', name: 'proximas_ediciones', defaults: ['titulo' => 'Próximas Ediciones'], methods: ['GET'])]
    public function listarProximasEdiciones(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): Response
    {
        /** @var Usuario|null $user */
        $user = $this->getUser();

        // Verificamos si el usuario tiene un participante asociado
        if (!$user || !$user->getParticipante()) {
            throw $this->createAccessDeniedException('No tienes acceso a esta sección.');
        }

        $participante = $user->getParticipante();

        // Obtenemos las ediciones futuras
        $proximasEdiciones = $entityManager->getRepository(Edicion::class)->findProximasEdiciones();

        return $this->render('intranet/forpas/participante/proximas_ediciones.html.twig', [
            'proximasEdiciones' => $proximasEdiciones,
            'participante' => $participante,
            'urlGenerator' => $urlGenerator,
        ]);
    }
}
