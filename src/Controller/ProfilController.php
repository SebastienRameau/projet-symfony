<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ModifierProfilType;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Proxies\__CG__\App\Entity\Participant as EntityParticipant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProfilController extends AbstractController
{
     /**
     * @Route("/profil/{id}", name="profil")
     */
    public function afficherProfil(Participant $participant): Response
    {


        return $this->render('profil/profil.html.twig', [

            'list_participant' => $participant,

        ]);
    }


    /**
     * @Route("/monprofil/", name="app_profil")
     */
    public function modifierProfil(Request $request, EntityManagerInterface $entityManagerInterface, UserPasswordHasherInterface $passwordHasher, SluggerInterface $slugger) : Response{
        /** 
          * @var Participant $participant 
          */
        $formModifierParticipant = $this->createForm(ModifierProfilType::class, $this->getUser());
        $formModifierParticipant->handleRequest($request);

        if ($formModifierParticipant->isSubmitted() && $formModifierParticipant->isValid()) {
            
            $password = $formModifierParticipant->get('password')->getData();
            // à revoir car la bdd n'enregistre pas le changement de password
            if ($password) {
                //pour hasher le mot de passe
                $this->getUser()->setPassword(
                    $passwordHasher->hashPassword(
                        $this->getUser(),
                        $formModifierParticipant->get('password')->getData()
                    )
                    );
                $password = $formModifierParticipant->getData();
                $entityManagerInterface->persist($this->getUser());
            }

            //changer de campus (en cours car la bdd n'enregistre pas le changement)
            // $campus = $formModifierParticipant->get('campus')->getData();
            // if ($campus) {
            //     $this->getUser()->setCampus();

            //     $campus = $formModifierParticipant->get('campus')->getData();
            //     $entityManagerInterface->persist($this->getUser());
            // }

            /** 
             * @var UploadedFile $photoFilename
             */
            $photoFilename = $formModifierParticipant->get('photoFilename')->getData();
            
            if ($photoFilename) {
                $originalFilename = pathinfo($photoFilename->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFilename->guessExtension();
                
                try {
                    $photoFilename->move(
                        $this->getParameter('photo_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                
                $this->getUser()->setPhotoFilename($newFilename);
                $entityManagerInterface->persist($this->getUser());
            
            }
            $entityManagerInterface->flush();
        }
        return $this->renderForm('profil/monprofil.html.twig', [
            'formModifierParticipant' => $formModifierParticipant,
        ]);
    }      
}