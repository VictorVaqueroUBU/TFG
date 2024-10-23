<?php

namespace App\Form\Gestor;

use App\Entity\Curso;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CursoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('codigo_curso')
            ->add('nombre_curso')
            ->add('horas')
            ->add('objetivos')
            ->add('contenidos')
            ->add('destinatarios')
            ->add('requisitos')
            ->add('justificacion')
            ->add('coordinador')
            ->add('participantes_edicion')
            ->add('ediciones_estimadas')
            ->add('plazo_solicitud')
            ->add('observaciones')
            ->add('visible_web')
            ->add('id_programa')
            ->add('horas_virtuales')
            ->add('calificable')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Curso::class,
        ]);
    }
}
