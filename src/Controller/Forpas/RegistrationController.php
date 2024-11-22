<?php

namespace App\Controller\Forpas;

use App\Entity\Forpas\Participante;
use App\Entity\Forpas\Usuario;
use App\Form\Forpas\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

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

        // Redirigir si el usuario ya está logado
        if ($this->getUser()) {
            $this->addFlash('warning', 'Ya estás logado. No puedes acceder a la página de registro.');
            return $this->redirectToRoute('intranet_index');
        }

        $user = new Usuario();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nif = $form->get('nif')->getData();

            // Verificamos si el NIF ya está registrado
            $existingParticipante = $entityManager->getRepository(Participante::class)->findOneBy(['nif' => $nif]);
            if ($existingParticipante) {
                $this->addFlash('warning', 'El NIF introducido ya está dado de alta.');
                return $this->redirectToRoute('intranet_register');
            }

            // Damos de alta al Usuario
            $passwordTemporal = substr(bin2hex(random_bytes(8)), 0, 8);
            $user->setPassword($passwordHasher->hashPassword($user, $passwordTemporal));
            $user->setRoles(['ROLE_USER']);
            $user->setVerified(False);

            // Damos de alta al Participante
            $participante = new Participante();
            $participante->setNif($nif);
            $participante->setNombre($form->get('nombre')->getData());
            $participante->setApellidos($form->get('apellidos')->getData());
            $participante->setUnidad('Nota: !Unidad == cesado. No permite inscripción');

            // Sincronizamos bidireccionalmente
            $user->setParticipante($participante);
            $participante->setUsuario($user);

            // Persistimos en la Base de Datos
            $entityManager->persist($user);
            $entityManager->persist($participante);
            $entityManager->flush();

            // Crear el correo
            $email = (new Email())
                ->from($this->params->get('mailer_sender'))
                ->to($user->getEmail())
                ->subject('Correo de registro en la aplicación Gestión Formación')
                ->html(sprintf(
                    '<p>Hola, %s %s. Su cuenta ha sido generada.'
                            . '<br><br>Para activarla debe entrar con la contraseña temporal <b>%s</b> e introducir una nueva.'
                            . ' <br><br>Por favor, inicie sesión y cámbiela.</p>',
                            $participante->getNombre(), $participante->getApellidos(), $passwordTemporal
                ));

            $mailer->send($email);

            // Redirigimos e informamos al usuario
            $this->addFlash('success', 'Alta realizada correctamente. Revise su correo electrónico');

            return $this->redirectToRoute('index');
        }

        return $this->render('intranet/registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
