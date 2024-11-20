<?php

namespace App\Form\Forpas;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nif', TextType::class, [
                'label' => 'NIF',
                'required' => true,
                'attr' => ['maxlength' => 9],
            ])
            ->add('nombre', TextType::class, [
                'label' => 'Nombre',
                'required' => true,
                'attr' => ['maxlength' => 50],
            ])
            ->add('apellidos', TextType::class, [
                'label' => 'Apellidos',
                'required' => true,
                'attr' => ['maxlength' => 50],
            ])
            ->add('organizacion', TextType::class, [
                'label' => 'Organización',
                'required' => true,
                'attr' => ['maxlength' => 50],
            ])
            ->add('correo1', EmailType::class, [
                'label' => 'Correo Electrónico',
                'required' => true,
                'attr' => ['maxlength' => 50],
            ])
            ->add('username', TextType::class, [
                'label' => 'Nombre de Usuario',
                'required' => true,
                'attr' => ['maxlength' => 180],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'label' => 'Contraseña',
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor, introduzca una contraseña',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Tu contraseña debe tener al menos {{ limit }} caracteres',
                        'max' => 255,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }
}
