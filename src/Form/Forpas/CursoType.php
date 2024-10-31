<?php

namespace App\Form\Forpas;

use App\Entity\Forpas\Curso;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class CursoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('codigo_curso', TextType::class, [
                'label' => 'Código *',
                'required' => true,
                'attr' => ['maxlength' => 5]
            ])
            ->add('nombre_curso', TextType::class, [
                'label' => 'Nombre *',
                'required' => true,
                'attr' => ['maxlength' => 255]
            ])
            ->add('horas', IntegerType::class, [
                'label' => 'Horas totales*',
                'required' => true,
                'attr' => ['inputmode' => 'numeric'],
                'constraints' => [
                    new Range([
                        'min' => 1,
                        'max' => 99,
                        'notInRangeMessage' => 'El valor debe estar entre {{ min }} y {{ max }}.',
                    ]),
                ],
            ])
            ->add('horas_virtuales',IntegerType::class, [
                'label' => 'Horas virtuales *',
                'required' => true,
                'constraints' => [
                    new Range([
                        'min' => 0,
                        'max' => 99,
                        'notInRangeMessage' => 'El valor debe estar entre {{ min }} y {{ max }}.',
                    ]),
                ],
            ])
            ->add('calificable', CheckboxType::class, [
                'label' => 'Evaluable', // Aquí defines el texto del label
                'required' => false,
            ])
            ->add('ediciones_estimadas',IntegerType::class, [
                'label' => 'Ediciones estimadas *',
                'required' => true,
                'constraints' => [
                    new Range([
                        'min' => 1,
                        'max' => 50,
                        'notInRangeMessage' => 'El valor debe estar entre {{ min }} y {{ max }}.',
                    ]),
                ],
            ])
            ->add('participantes_edicion', IntegerType::class, [
                'label' => 'Participantes edición *',
                'required' => true,
                'constraints' => [
                    new Range([
                        'min' => 1,
                        'max' => 150,
                        'notInRangeMessage' => 'El valor debe estar entre {{ min }} y {{ max }}.',
                    ]),
                ],
            ])
            ->add('visible_web',CheckboxType::class, ['required' => false])
            ->add('contenidos')
            ->add('destinatarios')
            ->add('requisitos')
            ->add('objetivos')
            ->add('justificacion', null, ['label' => 'Justificación',])
            ->add('plazo_solicitud',TextType::class, [
                'required' => false,
                'attr' => ['maxlength' => 255]
            ])
            ->add('coordinador',TextType::class, [
                'required' => false,
                'attr' => ['maxlength' => 255]
            ])
            ->add('observaciones')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Curso::class,
        ]);
    }
}
