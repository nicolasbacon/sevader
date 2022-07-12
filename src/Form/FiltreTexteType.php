<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreTexteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search',SearchType::class,[
                'required' => false,
                'label' => 'Le nom contient',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('rechercher',SubmitType::class,[
                'attr' => ['class' => 'submit btn btn-main'],
                'label' => 'Rechercher',

            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
