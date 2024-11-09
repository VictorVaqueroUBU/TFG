<?php

namespace App\Form\Forpas;

use App\Entity\Forpas\Edicion;
use App\Entity\Forpas\Participante;
use App\Entity\Forpas\ParticipanteEdicion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipanteEdicionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('participante', EntityType::class, [
                'class' => Participante::class,
                'disabled' => true,
                'choice_label' => function ($participante) {
                    return $participante->getNombre() . ' ' . $participante->getApellidos();
                },
            ])
            ->add('edicion', EntityType::class, [
                'class' => Edicion::class,
                'disabled' => true,
                'choice_label' => 'codigoEdicion',
            ])
            ->add('fecha_solicitud', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha de Solicitud',
                'required' => false,
            ])
            ->add('baja_justificada', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Baja Justificada',
                'required' => false,
            ])
            ->add('prueba_final', NumberType::class, [
                'label' => 'Prueba Final',
                'required' => false,
                'scale' => 2,
                'attr' => [
                    'step' => 0.01,
                    'min' => 0,
                    'max' => 10,
                ],
            ])
            ->add('certificado', ChoiceType::class, [
                'label' => 'Certificado',
                'required' => false,
                'choices' => [
                    'Sí' => 'S',
                    'No' => 'N',
                ],
            ])
            ->add('libro', IntegerType::class, [
                'label' => 'Libro',
                'required' => false,
                'attr' => ['placeholder' => 'YYYY'],
            ])
            ->add('numero_titulo', IntegerType::class, [
                'label' => 'Número de Título',
                'required' => false,
            ])
            ->add('observaciones', TextType::class, [
                'label' => 'Observaciones',
                'required' => false,
            ])
            ->add('apto', ChoiceType::class, [
                'label' => 'Prueba de Aptitud',
                'required' => false,
                'choices' => [
                    'Apto/a' => 1,
                    'No Apto/a' => 0,
                    'No Presentado/a' => -1,
                ],
            ])
            ->add('direccion', TextType::class, [
                'label' => 'Dirección',
                'required' => false,
                'attr' => ['maxlength' => 30],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ParticipanteEdicion::class,
        ]);
    }
}
