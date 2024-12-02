<?php

namespace App\Form\Forpas;

use App\Entity\Forpas\Participante;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipanteContactoType extends AbstractType
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
            ->add('puesto_trabajo', TextType::class, [
                'label' => 'Puesto de Trabajo',
                'disabled' => true,
            ])
            ->add('unidad', TextType::class, [
                'label' => 'Unidad',
                'disabled' => true,
            ])
            ->add('subunidad', TextType::class, [
                'label' => 'Subunidad',
                'disabled' => true,
            ])
            ->add('centro_destino', TextType::class, [
                'label' => 'Centro de Destino',
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
            ->add('telefono_trabajo', TextType::class, [
                'label' => 'Teléfono del Trabajo',
                'required' => false,
                'attr' => ['maxlength' => 30],
            ])
            ->add('telefono_particular', TextType::class, [
                'label' => 'Teléfono Particular',
                'required' => false,
                'attr' => ['maxlength' => 9],
            ])
            ->add('telefono_movil', TextType::class, [
                'label' => 'Teléfono Móvil',
                'required' => false,
                'attr' => ['maxlength' => 9],
            ])
        ;
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participante::class,
            'email' => null,
        ]);
    }
}
