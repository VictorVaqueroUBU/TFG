<?php

namespace App\Form\Forpas;

use App\Entity\Forpas\Formador;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormadorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nif', TextType::class, [
                'label' => 'NIF*',
                'required' => true,
                'attr' => ['maxlength' => 9],
            ])
            ->add('apellidos', TextType::class, [
                'label' => 'Apellidos*',
                'required' => true,
                'attr' => ['maxlength' => 50],
            ])
            ->add('nombre', TextType::class, [
                'label' => 'Nombre*',
                'required' => true,
                'attr' => ['maxlength' => 50],
            ])
            ->add('organizacion', TextType::class, [
                'label' => 'Organización*',
                'required' => true,
                'attr' => ['maxlength' => 50],
            ])
            ->add('correo', TextType::class, [
                'label' => 'Correo Auxiliar',
                'required' => false,
                'attr' => ['maxlength' => 50],
            ])
            ->add('telefono', TextType::class, [
                'label' => 'Teléfono del Trabajo',
                'required' => false,
                'attr' => ['maxlength' => 30],
            ])
            ->add('formadorRJ', ChoiceType::class, [
                'label' => 'Régimen Jurídico',
                'required' => false,
                'choices' => [
                    'Empleado Público' => 1,
                    'Autónomo en activo' => 2,
                    'Empresa/Sociedad' => 3,
                    'Persona física' => 4,
                ],
            ])
            ->add('observaciones', TextareaType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formador::class,
        ]);
    }
}
