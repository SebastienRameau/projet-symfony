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
    public function modifierProfil(Request $request, EntityManagerInterface $entityManagerInterface, UserPasswordHasherInterface $passwordHasher, SluggerInterface $slugger, CampusRepository $repoCampus) : Response{
        /** 
          * @var Participant $participant 
          */
        $participant = new Participant();

        
        $formModifierParticipant = $this->createForm(ModifierProfilType::class, $participant);
        $formModifierParticipant->handleRequest($request);

        if ($formModifierParticipant->isSubmitted() && $formModifierParticipant->isValid()) {

            $password = $formModifierParticipant->get('password')->getData();
            if ($password) {
                //pour hasher le mot de passe
                $participant->setPassword(
                    $passwordHasher->hashPassword(
                        $participant,
                        $formModifierParticipant->get('password')->getData()
                    )
                    );
                $entityManagerInterface->persist($participant);
            }

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
                
                $participant->setPhotoFilename($newFilename);
                $entityManagerInterface->persist($participant);
            
            }
            $entityManagerInterface->flush();
            
        }

        return $this->renderForm('profil/monprofil.html.twig', [
            'participant' => $participant,
            'formModifierParticipant' => $formModifierParticipant,
            // 'photoFilename' => $photoFilename

        ]);
   
    }      
}