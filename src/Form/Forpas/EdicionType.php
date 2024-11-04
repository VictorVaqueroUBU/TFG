<?php

namespace App\Form\Forpas;

use App\Entity\Forpas\Curso;
use App\Entity\Forpas\Edicion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class EdicionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $disabled = $options['disable_fields'];
        $builder
            ->add('codigo_edicion', TextType::class, [
                'label' => 'Código',
                'required' => true,
                'disabled' => true,
                'attr' => ['maxlength' => 8]
            ])
            ->add('fecha_inicio', null, [
                'widget' => 'single_text',
                'label' => 'Fecha de inicio',
                'disabled' => $disabled,
            ])
            ->add('fecha_fin', null, [
                'widget' => 'single_text',
                'label' => 'Fecha de fin',
                'disabled' => $disabled,
            ])
            ->add('lugar', null, [
                'disabled' => $disabled,
            ])
            ->add('calendario', null, [
                'disabled' => $disabled,
            ])
            ->add('horario', null, [
                'disabled' => $disabled,
            ])
            ->add('sesiones', IntegerType::class, [
                'label' => 'Nº de sesiones',
                'disabled' => $disabled,
                'constraints' => [
                    new Range([
                        'min' => 0,
                        'max' => 50,
                        'notInRangeMessage' => 'El valor debe estar entre {{ min }} y {{ max }}.',
                    ]),
                ],
            ])
            ->add('max_participantes', IntegerType::class, [
                'label' => 'Max. participantes',
                'disabled' => $disabled,
                'constraints' => [
                    new Range([
                        'min' => 0,
                        'max' => 150,
                        'notInRangeMessage' => 'El valor debe estar entre {{ min }} y {{ max }}.',
                    ]),
                ],
            ])
            ->add('estado', ChoiceType::class, [
                'label' => 'Estado',
                'disabled' => true,
                'choices' => [
                    'Abierta' => 0,
                    'Cerrada' => 1,
                    'Certificada' => 2,
                ],
            ])
            ->add('curso', EntityType::class, [
                'class' => Curso::class,
                'choice_label' => 'nombreCurso',
                'label' => 'Curso',
                'disabled' => $disabled,
                'placeholder' => 'Seleccione un curso',
                'required' => true,
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Edicion::class,
            'disable_fields' => false,
        ]);
    }
}
