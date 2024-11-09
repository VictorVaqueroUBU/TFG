<?php

namespace App\Form\Forpas;

use App\Entity\Forpas\Participante;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class ParticipanteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nif', TextType::class, [
                'label' => 'NIF',
                'required' => true,
                'attr' => ['maxlength' => 9],
            ])
            ->add('apellidos', TextType::class, [
                'required' => true,
                'attr' => ['maxlength' => 50],
            ])
            ->add('nombre', TextType::class, [
                'required' => true,
                'attr' => ['maxlength' => 50],
            ])
            ->add('descripcion_cce', TextType::class, [
                'label' => 'Descripción CCE',
                'required' => false,
                'attr' => ['maxlength' => 50],
            ])
            ->add('codigo_cce', TextType::class, [
                'label' => 'Código CCE',
                'required' => false,
                'attr' => ['maxlength' => 5],
            ])
            ->add('grupo', ChoiceType::class, [
                'label' => 'Grupo',
                'required' => false,
                'choices' => [
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                    '4' => 4,
                    'A1' => 'A1',
                    'A2' => 'A2',
                    'C1' => 'C1',
                    'C2' => 'C2',
                ],
            ])
            ->add('nivel', IntegerType::class, [
                'label' => 'Nivel',
                'required' => false,
                'constraints' => [
                    new Range([
                        'min' => 15,
                        'max' => 30,
                        'notInRangeMessage' => 'El valor debe estar entre {{ min }} y {{ max }}.',
                    ]),
                ],
            ])
            ->add('puesto_trabajo', TextType::class, [
                'label' => 'Puesto de Trabajo',
                'required' => false,
                'attr' => ['maxlength' => 75],
            ])
            ->add('subunidad', TextType::class, [
                'label' => 'Subunidad',
                'required' => false,
                'attr' => ['maxlength' => 50],
            ])
            ->add('unidad', TextType::class, [
                'label' => 'Unidad',
                'required' => false,
                'attr' => ['maxlength' => 50],
            ])
            ->add('centro_destino', TextType::class, [
                'label' => 'Centro de Destino',
                'required' => false,
                'attr' => ['maxlength' => 50],
            ])
            ->add('t_r_juridico', ChoiceType::class, [
                'label' => 'TR Jurídico',
                'required' => false,
                'choices' => [
                    'FI' => 'FI',
                    'FC' => 'FC',
                    'LE' => 'LE',
                    'LF' => 'LF',
                ],
            ])
            ->add('situacion_admin', TextType::class, [
                'label' => 'Situación Administrativa',
                'required' => false,
                'attr' => ['maxlength' => 75],
            ])
            ->add('codigo_plaza', TextType::class, [
                'label' => 'Código de la Plaza',
                'required' => false,
                'attr' => ['maxlength' => 8],
            ])
            ->add('telefono_trabajo', TextType::class, [
                'label' => 'Teléfono del Trabajo',
                'required' => false,
                'attr' => ['maxlength' => 30],
            ])
            ->add('correo_aux', TextType::class, [
                'label' => 'Correo Auxiliar',
                'required' => false,
                'attr' => ['maxlength' => 50],
            ])
            ->add('codigo_rpt', TextType::class, [
                'label' => 'Código en la RPT',
                'required' => false,
                'attr' => ['maxlength' => 16],
            ])
            ->add('organizacion', TextType::class, [
                'label' => 'Organización',
                'required' => false,
                'attr' => ['maxlength' => 100],
            ])
            ->add('turno', TextType::class, [
                'label' => 'Turno',
                'required' => false,
                'attr' => ['maxlength' => 50],
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
            ->add('fecha_nacimiento', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha de Nacimiento',
                'required' => false,
            ])
            ->add('titulacion_nivel', ChoiceType::class, [
                'label' => 'Titulación (Nivel)',
                'required' => false,
                'choices' => [
                    'Doctor' => 1,
                    'Licenciado' => 2,
                    'Diplomado' => 3,
                    'FP II/Bachiller' => 4,
                    'Graduado Escolar' => 5,
                    'Certificado Escolar' => 6,
                ],
            ])
            ->add('titulacion_fecha', DateType::class,  [
                'widget' => 'single_text',
                'label' => 'Titulación (Fecha)',
                'required' => false,
            ])
            ->add('titulacion', TextType::class, [
                'label' => 'Titulación',
                'required' => false,
                'attr' => ['maxlength' => 75],
            ])
            ->add('dni_sin_letra', TextType::class, [
                'label' => 'DNI sin letra',
                'required' => false,
                'attr' => ['maxlength' => 8],
            ])
            ->add('uvus', TextType::class, [
                'label' => 'UVUS',
                'required' => false,
                'attr' => ['maxlength' => 25],
            ])
            ->add('sexo', ChoiceType::class, [
                'label' => 'Sexo',
                'required' => false,
                'choices' => [
                    'Masculino' => 'V',
                    'Femenino' => 'M',
                ],
            ])
        ;
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participante::class,
        ]);
    }
}
