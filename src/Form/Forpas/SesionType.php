<?php

namespace App\Form\Forpas;

use App\Entity\Forpas\Sesion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SesionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fecha', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha de la sesión*',
                'required' => true,
            ])
            ->add('hora_inicio', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Hora de inicio*',
                'required' => true,
            ])
            ->add('duracionHoras', IntegerType::class, [
                'label' => 'Horas*',
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'max' => 100
                ],
            ])
            ->add('duracionMinutos', IntegerType::class, [
                'label' => 'Minutos*',
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'max' => 59
                ],
            ])
            ->add('observaciones', TextareaType::class, [
                'label' => 'Observaciones',
                'required' => false,
            ])
            ->add('tipo', ChoiceType::class, [
                'label' => 'Tipo de sesión*',
                'choices' => [
                    'Presencial' => 0,
                    'Virtual' => 1,
                ],
                'required' => true,
            ])
        ;
        // Evento para inicializar los campos horas y minutos a partir de la duración
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $sesion = $event->getData();
            $form = $event->getForm();

            if (!$sesion) {
                return;
            }

            $duracion = $sesion->getDuracion(); // en minutos
            $horas = $duracion !== null ? intdiv($duracion, 60) : 0;
            $minutos = $duracion !== null ? $duracion % 60 : 0;

            $form->get('duracionHoras')->setData($horas);
            $form->get('duracionMinutos')->setData($minutos);
        });

        // Evento POST_SUBMIT para asignar el valor en minutos a la entidad
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $sesion = $event->getData();
            $form = $event->getForm();

            $horas = $form->get('duracionHoras')->getData() ?? 0;
            $minutos = $form->get('duracionMinutos')->getData() ?? 0;

            $totalMinutos = ($horas * 60) + $minutos;
            $sesion->setDuracion($totalMinutos);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sesion::class,
        ]);
    }
}
