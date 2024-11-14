<?php

namespace App\Form\Forpas;

use App\Entity\Forpas\Edicion;
use App\Entity\Forpas\Formador;
use App\Entity\Forpas\FormadorEdicion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormadorEdicionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('formador', EntityType::class, [
                'class' => Formador::class,
                'disabled' => true,
                'choice_label' => function ($formador) {
                    return $formador->getApellidos() . ', ' . $formador->getNombre();
                },
                'label' => 'Formador',
            ])
            ->add('edicion', EntityType::class, [
                'class' => Edicion::class,
                'disabled' => true,
                'label' => 'Edición',
                'choice_label' => function ($edicion) {
                    return $edicion->getCodigoEdicion() . ' - ' . $edicion->getCurso()->getNombreCurso();
                },
            ])
            ->add('formadorRJ', TextType::class, [
                'label' => 'R.Jurídico',
                'mapped' => false,
                'data' => $builder->getData()->getFormador()->getFormadorRJ(),
                'disabled' => true,
            ])
            ->add('sin_coste',CheckboxType::class, [
                'label' => 'Sin Coste',
                'required' => false,
            ])
            ->add('incompatibilidad', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('datos_banco', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Datos Bancarios',
                'required' => false,
            ])
            ->add('hoja_firma', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Hoja de Firma',
                'required' => false,
            ])
            ->add('control_personal_enviado', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Control Personal Enviado',
                'required' => false,
            ])
            ->add('control_personal_recibido', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Control Personal Recibido',
                'required' => false,
            ])
            ->add('horas_impartidas', NumberType::class, [
                'label' => 'Horas Impartidas',
                'required' => false,
                'scale' => 2,
                'attr' => [
                    'step' => 0.01,
                    'min' => 0,
                    'max' => 200,
                ],
            ])
            ->add('retrib_prevista', NumberType::class, [
                'label' => 'Retribución Prevista',
                'required' => false,
                'scale' => 2,
                'attr' => [
                    'step' => 0.01,
                    'min' => 0,
                    'max' => 25000,
                ],
            ])
            ->add('retrib_ejecutada', NumberType::class, [
                'label' => 'Retribución Ejecutada',
                'required' => false,
                'scale' => 2,
                'attr' => [
                    'step' => 0.01,
                    'min' => 0,
                    'max' => 25000,
                ],
            ])
            ->add('grabado_sorolla', ChoiceType::class, [
                'label' => 'Grabado en Sorolla',
                'required' => false,
                'choices' => [
                    'SIN COSTE' => 'SIN COSTE',
                    'PENDIENTE' => 'PENDIENTE',
                    'FISCALIZADO' => 'FISCALIZADO',
                    'HECHO' => 'HECHO',
                ],
            ])
            ->add('fedap', ChoiceType::class, [
                'label' => 'Pago conforme a',
                'required' => false,
                'choices' => [
                    'Presupuesto propio' => false,
                    'Presupuesto FEDAP' => true,
                 ],
            ])
            ->add('evaluacion', ChoiceType::class, [
                'label' => 'Evaluación',
                'required' => false,
                'choices' => [
                    'Evalua visible' => 0,
                    'Evalua no visible' => 1,
                    'NO Evalua' => 2,
                ],
            ])
            ->add('coincide_turno', ChoiceType::class, [
                'label' => 'Compatibilidad turno',
                'disabled' => true,
                'choices' => [
                    '1) Durante mi jornada de trabajo' => 1,
                    '2) Fuera de mi jornada de trabajo' => 2,
                    '3) Durante y Fuera de mi jornada de trabajo' => 3,
                ],
            ])
            ->add('coincide_turno_observaciones', TextType::class, [
                'label' => 'Observaciones turno',
                'disabled' => true,
            ])
            ->add('observaciones', TextType::class, [
                'label' => 'Observaciones',
                'required' => false,
            ])
        ;
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FormadorEdicion::class,
        ]);
    }
}
