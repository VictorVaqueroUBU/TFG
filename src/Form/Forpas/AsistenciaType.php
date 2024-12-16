<?php

namespace App\Form\Forpas;

use App\Entity\Forpas\Asistencia;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AsistenciaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $disabled = $options['disabled'];
        $builder
            ->add('estado', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'Asiste' => 'asiste',
                    'Justifica' => 'justifica',
                    'No Asiste/No Justifica' => 'ninguno',
                ],
                'expanded' => true, // Mostrar como radios
                'multiple' => false,
                'disabled' => $disabled,
            ])
            ->add('observaciones', TextareaType::class, [
                'label' => 'Observaciones',
                'attr' => ['rows' => 1],
                'required' => false,
                'disabled' => $disabled,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Asistencia::class,
            'disabled' => false,
        ]);
    }
}