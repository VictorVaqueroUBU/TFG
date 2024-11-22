<?php

namespace App\Form\Forpas;

use App\Entity\Forpas\Usuario;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuario::class,
        ]);
    }
}
