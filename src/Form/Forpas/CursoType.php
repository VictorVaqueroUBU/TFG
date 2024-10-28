<?php

namespace App\Form\Forpas;

use App\Entity\Forpas\Curso;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CursoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('codigo_curso', null, ['label' => 'Código',])
            ->add('nombre_curso', null, ['label' => 'Nombre',])
            ->add('horas')
            ->add('participantes_edicion', null, ['label' => 'Participantes edición',])
            ->add('ediciones_estimadas')
            ->add('horas_virtuales')
            ->add('calificable')
            ->add('visible_web')
            ->add('contenidos')
            ->add('destinatarios')
            ->add('requisitos')
            ->add('objetivos')
            ->add('justificacion', null, ['label' => 'Justificación',])
            ->add('plazo_solicitud')
            ->add('coordinador')
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
