<?php

namespace App\Form\Forpas;

use App\Entity\Forpas\Formador;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormadorContactoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nif', TextType::class, [
                'label' => 'NIF*',
                'disabled' => true,
            ])
            ->add('apellidos', TextType::class, [
                'label' => 'Apellidos*',
                'disabled' => true,
            ])
            ->add('nombre', TextType::class, [
                'label' => 'Nombre*',
                'disabled' => true,
            ])
            ->add('organizacion', TextType::class, [
                'label' => 'Organización',
                'disabled' => true,
            ])
            ->add('email', TextType::class, [
                'label' => 'Correo Electrónico',
                'mapped' => false,
                'disabled' => true,
                'data' => $options['email'],
            ])
            ->add('correo_aux', TextType::class, [
                'label' => 'Correo Auxiliar',
                'required' => false,
                'attr' => ['maxlength' => 50],
            ])
            ->add('telefono', TextType::class, [
                'label' => 'Teléfono',
                'required' => false,
                'attr' => ['maxlength' => 30],
            ])
        ;
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formador::class,
            'email' => null,
        ]);
    }
}
