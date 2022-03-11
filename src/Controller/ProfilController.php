<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ModifierProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Proxies\__CG__\App\Entity\Participant as EntityParticipant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @Route("/monprofil", name="app_profil")
     */
    public function modifierProfil(Request $request, Participant $participant, EntityManagerInterface $em, SluggerInterface $slugger) : Response
    {
         /** 
          * @var Participant $participant 
          */
        $participant = $this->getUser();
        
        $form = $this->createForm(ModifierProfilType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** 
             * @var UploadedFile $photoFile
             */
            $photoFile = $form->get('photo')->getData();

            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();
                
                try {
                    $photoFile->move(
                        $this->getParameter('photo_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                
                $participant->setPhotoFilename($newFilename);
                $em->persist($participant);
                
                // if ($password) {
                //     # code...
                // }
            }
            $em->flush();
            return $this->redirectToRoute('accueil');
        }

        return $this->render('profil/monprofil.html.twig', [
            'form'=> $form
            // 'participant' => $participant,
        ]);
    }
}      