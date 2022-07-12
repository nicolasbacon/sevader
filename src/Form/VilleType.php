<?php

namespace App\Form;

use App\Entity\Ville;
use App\Service\CommunesFrance;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VilleType extends AbstractType
{
    private $communesFrance;

    /**
     * @param $communesFrance
     */
    public function __construct(CommunesFrance $communesFrance)
    {
        $this->communesFrance = $communesFrance;

       /* foreach ($communesFrance as $valeur ){
           echo $valeur;
       }
      die();*/
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => false,
                'attr' => ['class' => 'form-control'],
            ])
            /*->add('nom', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Choisir la ville dans la liste',
                'choices' => array_flip($this->communesFrance->getNomCommune())

            ])*/
            ->add('codePostal', TextType::class, [
                'label' => false,
                'attr' => ['class' => 'form-control'],

            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ville::class,
        ]);
    }
}
