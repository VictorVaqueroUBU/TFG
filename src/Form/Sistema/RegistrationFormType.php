<?php

namespace App\Form\Sistema;

use App\Entity\Sistema\Usuario;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nif', TextType::class, [
                'label' => 'NIF',
                'required' => true,
                'attr' => ['maxlength' => 9],
                'mapped' => false,
            ])
            ->add('nombre', TextType::class, [
                'label' => 'Nombre',
                'required' => true,
                'attr' => ['maxlength' => 50],
                'mapped' => false,
            ])
            ->add('apellidos', TextType::class, [
                'label' => 'Apellidos',
                'required' => true,
                'attr' => ['maxlength' => 50],
                'mapped' => false,
            ])
            ->add('organizacion', TextType::class, [
                'label' => 'Organización',
                'required' => true,
                'mapped' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Correo electrónico',
                'required' => true,
                'attr' => ['maxlength' => 50],
            ])
            ->add('username', TextType::class, [
                'label' => 'Usuario de acceso',
                'required' => true,
                'attr' => ['maxlength' => 180],
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Tipo de alta',
                'required' => true,
                'mapped' => false,
                'choices' => [
                    'Participante' => 'ROLE_USER',
                    'Formador' => 'ROLE_TEACHER',
                ],
            ])
            ->add('consent', CheckboxType::class, [
                'label' => 'Acepto el tratamiento de mis datos personales conforme a la <a href="/intranet/politica-privacidad" target="_blank">Política de Privacidad</a>.',
                'label_html' => true, // Habilitar HTML en el label
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Debes aceptar el tratamiento de tus datos personales.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuario::class,
        ]);
    }
}
