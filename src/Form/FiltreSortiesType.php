<?php

namespace App\Form;

use App\Classes\FiltreSorties;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreSortiesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus', EntityType::class, array(
                'class' => 'App:Campus',
                'placeholder' => '-- Choisir --',
                'choice_label' => 'nom',
                'required' => false,
                'label' => 'Campus : '
                ))
            ->add('nom',SearchType::class,[
                'required' => false,
                'label' => 'Le nom de la sortie contient : '
                ])
            ->add('dateMin', DateTimeType::class,[
                'label' => 'Entre ',
                'widget' => 'single_text',
                'required' => false,
                ])
            ->add('dateMax', DateTimeType::class,[
                'label' => ' et ',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('isOrganisateur', CheckboxType::class, array(
                'label'    => 'Sorties dont je suis l\'organisateur/trice',
                'required' => false,
                ))
            ->add('isInscrit', CheckboxType::class, array(
                'label'    => 'Sorties auxquelles je suis inscrit',
                'required' => false,
                ))
            ->add('isNonInscrit', CheckboxType::class, array(
                'label'    => 'Sorties auxquelles je ne suis pas inscrit',
                'required' => false,
                ))
            ->add('isPassee', CheckboxType::class, array(
                'label'    => 'Sorties passées',
                'required' => false,
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FiltreSorties::class,
        ]);
    }
}
