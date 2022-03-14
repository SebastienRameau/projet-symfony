<?php

namespace App\Controller;

use App\Classes\FiltreSorties;
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
use PhpParser\Node\Expr\Isset_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class SortieController extends AbstractController
{

    /**
     * @Route("/accueil", name="accueil")
     */
    public function sorties(
        Request $request,
        CampusRepository $repoCampus,
        SortieRepository $repoSortie,
        EtatRepository $repoEtat,
        ParticipantRepository $repoParticipant
    ): Response {

        //Envoyer la date du jour
        $date = explode("/", date('d/m/Y'));
        list($day, $month, $year) = $date;
        $dateJour = $day . '/' . $month . '/' . $year;


        //Envoyer le participant connecté
        $participantConnecte = $this->getUser();


        //Envoyer la liste des campus
        $campusListe = $repoCampus->findAll();


        //Gérer le bouton Créer une sortie
        //Non : rediriger vers la méthode de création au click sur le bouton


        //Créer et envoyer le formulaire de recherche de sorties
        $filtreSorties = new FiltreSorties();
        $form = $this->createForm(FiltreSortiesType::class, $filtreSorties);
        $form->handleRequest($request);

        //Click "Rechercher"
        if ($form->isSubmitted()) {
            $sortiesListe = $repoSortie->findByFilters($filtreSorties, $participantConnecte, $dateJour);
            return $this->render('sortie/accueil.html.twig', [
                'date_jour' => $dateJour,
                'participant_connecte' => $participantConnecte,
                'campus_liste' => $campusListe,
                'form' => $form->createView(),
                'sorties_liste' => $sortiesListe,
            ]);
        }

        $sortiesListe = $repoSortie->findByFilters($filtreSorties, $participantConnecte, $dateJour);
        return $this->render('sortie/accueil.html.twig', [
            'date_jour' => $dateJour,
            'participant_connecte' => $participantConnecte,
            'campus_liste' => $campusListe,
            'form' => $form->createView(),
            'sorties_liste' => $sortiesListe,
        ]);
    }



    /**
     * @Route("/annuler/{id}", name="annuler_sortir")
     */
    public function annuler_sortir(Sortie $sortie, EtatRepository $etatRepo, Request $rq, EntityManagerInterface $emi): Response
    {

        $form = $this->createForm(AnnulerFormType::class, $sortie);

        $form->handleRequest($rq);


        if ($form->isSubmitted()) {

            // $time = date('H:i:s \O\n d/m/Y');
            // $dateDebut = $sortie->getDateHeureDebut();

            // if($time > $dateDebut){


            $etat = $etatRepo->findOneBy(['libelle' => 'Annulée']);

            $sortie->setEtat($etat);

            $emi->flush();
            return $this->redirectToRoute('accueil');
        }

        // // $this->addFlash(
        // //     'notice',
        // //     ' Vous ne pouvez pas annuler cette sortie parce que celle-ci a deja commencée'

        // );


        return $this->render('sortie/annuler.html.twig', [

            'formular' => $form->createView(),
            'list_sortie' => $sortie

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
