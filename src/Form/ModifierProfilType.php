<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Repository\CampusRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class ModifierProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo')
            ->add('prenom')
            ->add('nom')
            ->add('telephone')
            ->add('mail')
            ->add('password', RepeatedType::class, [
                'attr' => [
                    'type' => PasswordType::class,
                    'invalid-message' => "Le mot de passe n'est pas identique",
                    'options' => ['attr' => ['class' => 'password-field']],
                    'required' => false,
                    'first_options' => array('label' => 'Mot de passe'),
                    'second_options' => array('label' => 'Confirmation'),
                 ]
            ]
            )
            
            //Ajouter la liste déroulante des campus présents en BDD
             ->add('campus', EntityType::class, [
                'mapped' => false,
                'class' => Campus::class,
                'choice_label' => 'nom',
                'placeholder' => '-- Choisir --',
                'required' => false,
            ])
            
            //ajouter une photo (qui doit être enregistrer dans le dossier public/uploads pour les tests)    
            ->add('photoFilename', FileType::class, [
                    'label' => 'Ma photo :',
                    // 'placeholder' => 'Télécharger vers le serveur',
                    'required' => false,
                    'constraints' => [
                        new Image([
                            'maxSize' => '1024k',
                            'mimeTypes' => [
                                'image/jpg',
                                'image/png',
                            ],
                            'mimeTypesMessage' => 'Veuillez charger un fichier valide',
                        ]),
                    ]
            ])
            
            ->getForm();
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
