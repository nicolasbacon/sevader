<?php

namespace App\Form;

use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('site', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom'
            ])
            ->add('textSearch', SearchType::class, [
                'label' => 'Le nom de la sortie contient : ',
                'attr' => ['placeholder' => 'Recherche'
                ],
                'required' => false
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Entre le ',
                'widget' => 'single_text',
                'html5' => true,
                'required' => false
            ])
            ->add('endDate', DateType::class, [
                'label' => 'et le ',
                'widget' => 'single_text',
                'html5' => true,
                'required' => false
            ])
            ->add('organizer', CheckboxType::class, [
                'label' => 'Sorties dont je suis l\'organisateur.rice',
                'required' => false
            ])
            ->add('subscription', ChoiceType::class, [
                'label' => 'Inscription à la sortie',
                'choices' => [
                    'Je suis inscrit' => 'registered',
                    'Je ne suis pas inscrit' => 'unregistered',
                ],
                'required'=> false,
                'placeholder' => 'toutes les sorties',
                'expanded' => true

            ])
            ->add('ended', CheckboxType::class, [
                'label' => 'Sorties passées',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
