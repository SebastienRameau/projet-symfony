<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CreerSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\NotBlank,
                    new Assert\Length([
                        'min' => 1,
                        'max' => 50,
                    ]),
                ],
            ])

            ->add('dateHeureDebut', DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                // prevents rendering it as type="date", to avoid HTML5 date pickers
                'html5' => true,
            ])

            ->add('duree')

            ->add('dateLimiteInscription' , DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                // prevents rendering it as type="date", to avoid HTML5 date pickers
                'html5' => true,
            ])

            ->add('nbInscriptionMax')

            ->add('infosSortie', TextareaType::class, [
                'required' => false,
                'attr' => array('rows' => '5','cols' => '40'),
            ])

            ->add('campus', EntityType::class,[
                'class' => Campus::class,
                'choice_label' => 'nom',
            ])
        
            ->add('lieu', EntityType::class, [
                'class'=> Lieu::class,
                'choice_label' => 'nom',
            ])

            ->add('enregistrer', SubmitType::class, [
                'label' => 'Enregistrer',
            ])

            ->add('publier', SubmitType::class, [
                'label' => 'Publier',
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
