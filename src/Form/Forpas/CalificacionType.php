<?php
namespace App\Form\Forpas;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CalificacionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('apto', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'Apto' => 1,
                    'No Apto' => 0,
                    'No Presentado' => -1,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('pruebaFinal', NumberType::class, [
                'label' => 'Nota (opcional)',
                'required' => false,
                'scale' => 2,
                'attr' => ['min' => 0, 'max' => 10],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
