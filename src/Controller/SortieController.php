<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Form\FiltreSortiesType;
use App\Entity\Sortie;
use App\Form\AnnulerFormType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use FiltreSorties;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/accueil", name="accueil")
     */
    public function sorties(CampusRepository $repoCampus, SortieRepository $repoSortie, EtatRepository $repoEtat,
         ParticipantRepository $repoParticipant): Response
    {
        //Envoyer la date du jour
        $date = explode("/",date('d/m/Y'));
        list($day,$month,$year) = $date;
        $dateJour = $day.'/'.$month.'/'.$year;

        
        //Envoyer le participant connecté (Voir plus tard, quand Estelle aura fait la connexion)
        $participantConnecte = $repoParticipant->findOneBy(['id' => '1']); //temporaire


        //Envoyer la liste des campus
        $campusListe = $repoCampus->findAll();

        //Envoyer la liste des sorties Sauf historisées Ou non publiées (créées) (sauf si le participant est l'organisateur)
        // $requete = $repoSortie->createQueryBuilder('p')
        //     ->select('p')
        //     ->where('p.etat = '.$repoEtat->findOneBy(['libelle' => 'Ouverte'])->getId())
        //     ->orWhere('p.etat = '.$repoEtat->findOneBy(['libelle' => 'Cloturée'])->getId())
        //     ->orWhere('p.etat = '.$repoEtat->findOneBy(['libelle' => 'Activité en cours'])->getId())
        //     ->orWhere('p.etat = '.$repoEtat->findOneBy(['libelle' => 'Passée'])->getId())
        //     ->orWhere('p.etat = '.$repoEtat->findOneBy(['libelle' => 'Annulée'])->getId())
        //     ->orWhere('p.organisateur = '.$participantConnecte->getId())
        //     ->orderBy('p.dateHeureDebut', 'ASC')
        //     ->getQuery();

        // $sortiesListe = $requete->getResult();

        //Créer et envoyer le formulaire de recherche de sorties
        $sortiesListe = [];
        $filtreSorties = new FiltreSorties();
        $form = $this->createForm(FiltreSortiesType::class, $filtreSorties);
        if ($form->isSubmitted() && $form->isValid()) {
            $sortiesListe = $repoSortie->findByFilters($filtreSorties);
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
    public function annuler_sortir(Sortie $sortie, Request $rq, EntityManagerInterface $emi): Response
    {

        $form = $this->createForm(AnnulerFormType::class, $sortie);

        $form->handleRequest($rq);

        if ($form->isSubmitted()){

            $time = date('H:i:s \O\n d/m/Y');
            $timeDebut->getDategio
            
            if($time > $dateHeureDebut){


                $emi->flush();
                return $this->redirectToRoute('acceuil');

            }


           
        }




        return $this->render('sortie/annuler.html.twig', [

            'sorties_liste' => $sortie,

        ]);
    }


    /**
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(Wish $wish, Request $rq, EntityManagerInterface $emi): Response
    {
        $form= $this->createForm(WishFormType::class,$wish);

        $form->handleRequest($rq);

        if($form -> isSubmitted()){

            $this->addFlash(
                'notice1',
                'Your article is edited'

            );


            $emi->flush();
            return $this->redirectToRoute('wish');

           

        }

        return $this->render('wish/edit.html.twig', [
            'formular'=> $form->createView()
        ]);

    }

    // /**
    //  * @Route("/favourite/", name="favourite")
    //  */
    // public function favourite(Request $rq, EntityManagerInterface $emi): Response
    // {
    //     $wish= new Wish();
    //     $form = $this->createForm(WishFormType::class, $wish);

        

    //     $form->handleRequest($rq);

    //     if ( $form -> isSubmitted() && $form->isValid())
    //     {
            
    //         $age = $form->get('age')->getData();
    //         if($age < 18)
    //         {
    //             $this->addFlash(
    //                 'notice',
    //                 'You are too young to join this club! You can not add anything to our list'

    //             );

    //         }else{

    //             $this->addFlash(
    //                 'notice',
    //                 'Your Article was added'

    //             );



    //         $emi->persist($wish);
    //         $emi->flush();
    //         return $this->redirectToRoute('wish');
    //         }

    //     }

    //     return $this->render('wish/favourite.html.twig', [
    //         'formular'=> $form->createView(),
    //     ]);


    // }
}
