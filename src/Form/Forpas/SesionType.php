<?php

namespace App\Form\Forpas;

use App\Entity\Forpas\Sesion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SesionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fecha', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha de la sesión',
                'required' => true,
            ])
            ->add('hora_inicio', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Hora de inicio',
                'required' => true,
            ])
            ->add('duracion', NumberType::class, [
                'label' => 'Duración (horas)',
                'html5' => true,
                'required' => true,
                'scale' => 2,
                'attr' => [
                    'step' => 0.01,
                    'min' => 0,
                    'max' => 100,
                ],
            ])
            ->add('observaciones', TextareaType::class, [
                'label' => 'Observaciones',
                'required' => false,
            ])
            ->add('tipo', ChoiceType::class, [
                'label' => 'Tipo de sesión',
                'choices' => [
                    'Presencial' => 0,
                    'Virtual' => 1,
                ],
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sesion::class,
        ]);
    }
}
