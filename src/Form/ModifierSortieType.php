<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

use Symfony\Component\Validator\Constraints as Assert;



class ModifierSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\NotBlank(),

                    new Assert\Length([
                        'min' => 5,
                        'max' => 100,
                    ]),
                ],
            ])

            ->add('dateHeureDebut', DateTimeType::class, [
             
                'required' => false,
                
                'date_widget' => 'single_text',

                'time_widget' => 'single_text',
               
                // 'html5' => true,

                // 'input'  => 'datetime_immutable',

            ])



            ->add('duree')
            

            ->add('dateLimiteInscription' , DateType::class, [
             
                'required' => false,
                'widget' => 'single_text',

                // prevents rendering it as type="date", to avoid HTML5 date pickers

                // 'html5' => false,

                // 'input'  => 'datetime_immutable',

                'attr' =>[
                    'class' => 'js-datepicker',
                    'data-provide' => 'datetimepicker', ],

            ])

            ->add('nbInscriptionMax')

            ->add('infosSortie', TextareaType::class, [
                'required' => false,
                'attr' => array('rows' => '5','cols' => '50'),
            ])

        //    ->add('participants')
        //    ->add('organisateur')

            ->add('campus', EntityType::class,[
                'class' => Campus::class,
                'choice_label' => 'nom',
            ])
        

            ->add('lieu', EntityType::class, [
                'class'=> Lieu::class,
                'choice_label' => 'nom',

            ])


            // ->add('latitude', EntityType::class, [
            //     'class'=> Lieu::class,
            //     'choice_label' => 'latitude',

            // ])

            // ->add('longitude', EntityType::class,[
            //     'class'=> Lieu::class,
            //     'choice_label' => 'longitude'
            // ])

            




        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
