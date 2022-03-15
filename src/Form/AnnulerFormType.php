<?php

namespace App\Form;

use App\Entity\Sortie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnulerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('nom')
            // ->add('dateHeureDebut')
           
            // ->add('campus')
        //    ->add('etat')
            // ->add('lieu')

            // ->add('motif' , TextareaType::class, [
            //     'mapped' => false,
            //     'attr' => array('rows' => '5','cols' => '50'),
            //     'label' => 'Motif'   
            // ]
            // )

            ->add('infosSortie', TextareaType::class, [
                'mapped' => true,
                'required' => false,

                'attr' => array('rows' => '5','cols' => '50'),
                'label' => 'Motif'

            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
