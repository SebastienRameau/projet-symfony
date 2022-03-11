<?php

namespace App\Controller;

use App\Classes\FiltreSorties as ClassesFiltreSorties;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Form\FiltreSortiesType;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\AnnulerFormType;
use App\Form\ModifierSortieType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use FiltreSorties;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class SortieController extends AbstractController
{
    /**
     * @Route("/accueil", name="accueil")
     */
    public function sorties(Request $request, CampusRepository $repoCampus, SortieRepository $repoSortie, EtatRepository $repoEtat,
         ParticipantRepository $repoParticipant): Response
    {
        //Envoyer la date du jour
        $date = explode("/",date('d/m/Y'));
        list($day,$month,$year) = $date;
        $dateJour = $day.'/'.$month.'/'.$year;

        
        //Envoyer le participant connecté (Voir plus tard, quand Estelle aura fait la connexion)

        $participantConnecte = $repoParticipant->findOneBy(['id' => '43']); //temporaire



        //Envoyer la liste des campus
        $campusListe = $repoCampus->findAll();


        //Créer et envoyer le formulaire de recherche de sorties
        $sortiesListe = [];
        $filtreSorties = new ClassesFiltreSorties();
        $form = $this->createForm(FiltreSortiesType::class, $filtreSorties);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sortiesListe = $repoSortie->findByFilters($filtreSorties, $repoEtat, $repoCampus, $participantConnecte);
            return $this->redirectToRoute('accueil');
        }

        //Gérer le bouton Rechercher
        //Ou tout faire en Javascript ?


        //Gérer le bouton Créer une sortie
        //Non : rediriger vers la méthode de création au click sur le bouton


        return $this->render('sortie/accueil.html.twig', [
            'date_jour' => $dateJour,
            'participant_connecte' => $participantConnecte,
            'campus_liste' => $campusListe,
            'sorties_liste' => $sortiesListe,
            'form' => $form->createView(),
        ]);
    }



    /**
     * @Route("/annuler/{id}", name="annuler_sortir")
     */
    public function annuler_sortir(Sortie $sortie,EtatRepository $etatRepo, Request $rq, EntityManagerInterface $emi): Response
    {

        $form = $this->createForm(AnnulerFormType::class, $sortie);

        $form->handleRequest($rq);


        if ($form->isSubmitted()){

            // $time = date('H:i:s \O\n d/m/Y');
            // $dateDebut = $sortie->getDateHeureDebut();
            
            // if($time > $dateDebut){


                $etat=$etatRepo->findOneBy(['libelle'=> 'Annulée']);

                $sortie->setEtat($etat);

                $emi->flush();
                return $this->redirectToRoute('acceuil');

            }
            
            // // $this->addFlash(
            // //     'notice',
            // //     ' Vous ne pouvez pas annuler cette sortie parce que celle-ci a deja commencée'

            // );

    
        return $this->render('sortie/annuler.html.twig', [

            'formular'=> $form->createView(),
            'list_sortie'=> $sortie
            
        ]);
    }

    /**
     * @Route("/modifier/{id}", name="modifier_sortir")
     */
    public function modifier_sortir(Sortie $sortie,EtatRepository $etatRepo, Request $rq, EntityManagerInterface $emi): Response
    {

        $form = $this->createForm(ModifierSortieType::class,$sortie);

        $form->handleRequest($rq);


        if ($form->isSubmitted()){

              
                $emi->flush();
                return $this->redirectToRoute('acceuil');

            }
            
            $this->addFlash(
                'notice',
                ' Vous avez deja modifie une sortie'

            );

    
        return $this->render('sortie/modifiersortie.html.twig', [

            'formular'=> $form->createView(),
            'list_sortie'=> $sortie
            
        ]);
    }




}
