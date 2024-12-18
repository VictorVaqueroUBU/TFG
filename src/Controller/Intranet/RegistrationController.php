<?php

namespace App\Controller\Intranet;

use App\Entity\Forpas\Formador;
use App\Entity\Forpas\Participante;
use App\Entity\Sistema\Usuario;
use App\Form\Sistema\RegistrationFormType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controlador para gestionar el registro del usuario
 * @author Víctor M. Vaquero <vvm1002@alu.ubu.es>
 */
class RegistrationController extends AbstractController
{
    private ParameterBagInterface $params;
    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }
    /**
     * @throws TransportExceptionInterface
     * @throws RandomException
     */
    #[Route('/intranet/register', name: 'intranet_register', defaults: ['titulo' => 'Página de registro'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        UserPasswordHasherInterface $passwordHasher
    ): Response {

        // Redirigir si el usuario ya ha iniciado sesión
        if ($this->getUser()) {
            $this->addFlash('warning', 'Ya has iniciado sesión. No puedes acceder a la página de registro.');
            return $this->redirectToRoute('intranet');
        }

        $user = new Usuario();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nif = $form->get('nif')->getData();
            $role = $form->get('role')->getData();

            // Verificamos si el NIF ya está registrado en la tabla correspondiente
            $repository = $role === 'ROLE_USER' ? Participante::class : Formador::class;
            $existingEntity = $entityManager->getRepository($repository)->findOneBy(['nif' => $nif]);
            if ($existingEntity) {
                $this->addFlash('warning', 'El NIF introducido ya está registrado.');
                return $this->redirectToRoute('intranet_register');
            }

            // Damos de alta al Usuario
            $passwordTemporal = substr(bin2hex(random_bytes(8)), 0, 8);
            $user->setPassword($passwordHasher->hashPassword($user, $passwordTemporal));
            $user->setRoles([$role]);
            $user->setVerified(false);
            $user->setCreatedAt(new DateTimeImmutable('now'));

            // Creamos la entidad correspondiente
            if ($role === 'ROLE_USER') {
                $entity = new Participante();
                $entity->setUnidad('Nota: !Unidad == cesado. No permite inscripción');
            } else {
                $entity = new Formador();
            }

            $entity->setNif($nif);
            $entity->setNombre($form->get('nombre')->getData());
            $entity->setApellidos($form->get('apellidos')->getData());
            $entity->setOrganizacion($form->get('organizacion')->getData());

            // Sincronización bidireccional
            if ($role === 'ROLE_USER') {
                $user->setParticipante($entity);
            }else {
                $user->setFormador($entity);
            }
            $entity->setUsuario($user);

            // Persistimos en la base de datos
            $entityManager->persist($user);
            $entityManager->persist($entity);
            $entityManager->flush();

            // Enviar correo
            $this->sendRegistrationEmail($mailer, $user, $passwordTemporal);

            // Redirigimos e informamos al usuario
            $this->addFlash('success', 'Alta realizada correctamente. Revise su correo electrónico');

            return $this->redirectToRoute('intranet_login');
        }

        return $this->render('/intranet/sistema/registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function sendRegistrationEmail(MailerInterface $mailer, Usuario $user, string $passwordTemporal): void
    {
        // Creamos el correo
        $email = (new Email())
            ->from($this->params->get('mailer_sender'))
            ->to($user->getEmail())
            ->subject('Registro en la aplicación Gestión Formación')
            ->html(sprintf(
                '<p>Bienvenido, %s. Su cuenta ha sido creada.<br><br>'.
                    'Inicie sesión con la contraseña temporal <b>%s</b> para activar su cuenta y poder cambiar la contraseña.</p>',
                $user->getUsername(),
                $passwordTemporal
            ));
        $mailer->send($email);
    }
}
