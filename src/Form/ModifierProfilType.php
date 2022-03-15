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
            ->add('pseudo', null,[
                'attr' => [
                    'required' => false
                ]])
            ->add('prenom', null,[
                'attr' => [
                    'required' => false
                ]])
            ->add('nom', null,[
                'attr' => [
                    'required' => false
                ]])
            ->add('telephone', null,[
                'attr' => [
                    'required' => false
                ]])
            ->add('mail', null,[
                'attr' => [
                    'required' => false
                ]])

            // permet ici d'appeler une première class RepeatedType qui va automatiquement mettre deux champs "First (mot de passe) et Second (confirmation)"
            // on précise le type pour lui dire que ce sera la class PasswordType (permet de voir le mot de passe sous forme de points noirs)
            ->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    // 'invalid-message' => "Le mot de passe n'est pas identique",
                    'options' => ['attr' => ['class' => 'password-field']],
                    'required' => false,
                    'first_options' => array('label' => 'Mot de passe'),
                    'second_options' => array('label' => 'Confirmation'),
                    'mapped' => false,
            ]
            )
            
            //Ajouter la liste déroulante des campus présents en BDD
             ->add('campus', EntityType::class, [
                // ne pas le décommenter car sinon n'envoit pas en BDD
                // 'mapped' => false,
                'class' => Campus::class,
                'choice_label' => 'nom',
                'placeholder' => '-- Choisir --',
                'required' => false,
            ])
            
            //ajouter une photo (qui doit être enregistrer dans le dossier public/uploads pour les tests)    
            ->add('photoFilename', FileType::class, [
                    'mapped' => false,
                    'label' => 'Ma photo :',
                    'required' => false,
                    'constraints' => [
                        new Image([
                            'maxSize' => '1024k',
                            //en commentaire car génère une erreur même si le type est bon
                            'mimeTypes' => [
                                'image/jpg',
                                'image/png',
                            ],
                            //permet ici de mettre un message quand la photo ne correspond pas au format souhaité
                            'mimeTypesMessage' => 'Veuillez charger une photo valide',
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
