<?php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
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
                'type' => PasswordType::class,
                'invalid-message' => "Le mot de passe n'est pas identique",
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => false,
                'first_options' => ['label' => ''],
                'second_options' => ['label' => ''],
            ])
            ->add('campusNom', ChoiceType::class, array(
                'choices' => array('Saint Herblain' => 'Saint Herblain', 'Chartres de Bretagne' => 'Chartres de Bretagne',
                 'La Roche sur Yon' => 'La Roche sur Yon'),
                ))
            ->add('photo', FileType::class, [
                    'label' => 'Télécharger vers le serveur',
                    'mapped' => false,
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
