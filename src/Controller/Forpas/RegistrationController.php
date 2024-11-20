<?php

namespace App\Controller\Forpas;

use App\Entity\Forpas\Formador;
use App\Entity\Forpas\Participante;
use App\Entity\Forpas\Usuario;
use App\Form\Forpas\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/intranet/register', name: 'intranet_register', defaults: ['titulo' => 'Página de registro'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Damos de alta al Usuario
            $user = new Usuario();
            $user->setCorreo1($form->get('correo1')->getData());
            $user->setUsername($form->get('username')->getData());
            $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData()));
            $user->setRoles(['ROLE_ADMIN', 'ROLE_USER', 'ROLE_TEACHER']);

            // Damos de alta al Participante
            $participante = new Participante();
            $participante->setNif($form->get('nif')->getData());
            $participante->setNombre($form->get('nombre')->getData());
            $participante->setApellidos($form->get('apellidos')->getData());
            $participante->setUnidad('Nota: !Unidad == cesado. No permite inscripción');

            // Damos de alta al Formador
            $formador = new Formador();
            $formador->setNif($form->get('nif')->getData());
            $formador->setNombre($form->get('nombre')->getData());
            $formador->setApellidos($form->get('apellidos')->getData());
            $formador->setOrganizacion('Nombre de la empresa');

            // Sincronizamos bidireccionalmente
            $user->setParticipante($participante);
            $participante->setUsuario($user);
            $user->setFormador($formador);
            $formador->setUsuario($user);

            // Persistimos en la Base de Datos
            $entityManager->persist($user);
            $entityManager->persist($participante);
            $entityManager->persist($formador);

            // Almacenamos en la base de datos
            $entityManager->flush();

            // Redirigimos e informamos al usuario
            $this->addFlash('success', 'Usuario dado de alta correctamente.');

            return $this->redirectToRoute('index');
        }

        return $this->render('intranet/registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
