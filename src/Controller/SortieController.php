<?php

namespace App\Controller;

use App\Classes\FiltreSorties;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Form\FiltreSortiesType;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\AnnulerFormType;
use App\Form\CreerSortieType;
use App\Form\ModifierSortieType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PhpParser\Node\Expr\Isset_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class SortieController extends AbstractController
{

    /**
     * @Route("/accueil", name="accueil")
     */
    public function sorties(
        Request $request,
        CampusRepository $repoCampus,
        SortieRepository $repoSortie
    ): Response {

        //Envoyer la date du jour
        $date = explode("/", date('d/m/Y'));
        list($day, $month, $year) = $date;
        $dateJour = $day . '/' . $month . '/' . $year;


        //Envoyer le participant connecté
        $participantConnecte = $this->getUser();

        //Envoyer la liste des campus
        $campusListe = $repoCampus->findAll();


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

        //Pour qu'au premier chargement de la page, seules les sorties auxquelles le participant connecté est inscrit s'affichent.
        $filtreSorties->setIsInscrit(true);
        
        $sortiesListe = $repoSortie->findByFilters($filtreSorties, $participantConnecte, $dateJour);

        //Réinitialise filtreSorties, pour que ses champ soient vides avant de valider le formulaire
        $filtreSorties = new FiltreSorties();

        return $this->render('sortie/accueil.html.twig', [
            'date_jour' => $dateJour,
            'participant_connecte' => $participantConnecte,
            'campus_liste' => $campusListe,
            'form' => $form->createView(),
            'sorties_liste' => $sortiesListe,
        ]);
    }


    /**
     * @Route("/afficher/{id}", name="afficher_sortie")
     */
    public function afficherSortie(Sortie $sortie): Response
    {
        return $this->render('sortie/afficherSortie.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    /**
     * @Route("/publier/{id}", name="publier_sortie")
     */
    public function publierSortie(Sortie $sortie, EtatRepository $etatRepo, EntityManagerInterface $em): Response
    {
        //Contrôle de l'identité de l'organisateur
        $participantConnecte = $this->getUser();
        if ($sortie->getOrganisateur() != $participantConnecte) {
            $this->addFlash(
                'notice',
                'Vous n\'êtes pas autorisé à publier cette sortie !'
            );
            return $this->redirectToRoute('accueil');
        }

        $sortie->setEtat($etatRepo->findOneBy(['libelle' => 'Ouverte']));

        $this->addFlash(
            'notice',
            'La sortie a été publiée'
        );

        $em->persist($sortie);
        $em->flush();

        return $this->redirectToRoute('accueil');
    }    


    /**
     * @Route("/creer", name="creer_sortie")
     */
    public function creerSortie(EtatRepository $etatRepo, Request $request, EntityManagerInterface $em): Response
    {
        $participantConnecte = $this->getUser();

        $nouvelleSortie = new Sortie();

        $formular = $this->createForm(CreerSortieType::class, $nouvelleSortie);

        $formular->handleRequest($request);

        if ($formular->isSubmitted() && $formular->isValid()) {
            //Si on a cliqué sur Enregistrer, la sortie est créée avec l'état "Créée" (donc, non publiées)
            if ($formular->getClickedButton() === $formular->get('enregistrer')) { // getClickedButton fonctionne malgré le soulignement rouge.
                $nouvelleSortie->setEtat($etatRepo->findOneBy(['libelle' => 'Créée']));
                $this->addFlash(
                    'notice',
                    'La sortie a été enregistrée comme "en cours de création"'
                );
            }
            //Si on a cliqué sur Publier, la sortie est créée avec l'état "Ouverte" (donc publiée et accessible par les autres utilisateurs)
            if ($formular->getClickedButton() === $formular->get('publier')) {
                $nouvelleSortie->setEtat($etatRepo->findOneBy(['libelle' => 'Ouverte']));
                $this->addFlash(
                    'notice',
                    'La sortie a été créée et publiée'
                );
            }
            //Le participant connecté est ajouté en temps qu'organisateur...
            $nouvelleSortie->setOrganisateur($participantConnecte);
            // ...et premier inscrit de la sortie
            $nouvelleSortie->addParticipant($participantConnecte);
            $em->persist($nouvelleSortie);
            $em->flush();
            return $this->render('sortie/creerSortie.html.twig', [
                'formular' => $formular->createView(),
            ]);
        }

        return $this->render('sortie/creerSortie.html.twig', [
            'formular' => $formular->createView(),
        ]);
    }



    /**
     * @Route("/annuler/{id}", name="annuler_sortir")
     */
    public function annuler_sortir(Sortie $sortie, EtatRepository $etatRepo, Request $rq, EntityManagerInterface $emi): Response
    {
        //Contrôle de l'identité de l'organisateur
        $participantConnecte = $this->getUser();
        if ($sortie->getOrganisateur() != $participantConnecte) {
            $this->addFlash(
                'notice',
                'Vous n\'êtes pas autorisé à annuler cette sortie !'
            );
            return $this->redirectToRoute('accueil');
        }

        $form = $this->createForm(AnnulerFormType::class, $sortie);

        $form->handleRequest($rq);


        if ($form->isSubmitted()) {

            $this->addFlash(
                'notice',
                'Vous avez annulé une sortie'
            );


            $etat = $etatRepo->findOneBy(['libelle' => 'Annulée']);
                
            $sortie->setEtat($etat);

            $emi->flush();
            return $this->redirectToRoute('accueil');
        }



        return $this->render('sortie/annuler.html.twig', [

            'formular' => $form->createView(),
            'list_sortie' => $sortie

        ]);
    }

    /**
     * @Route("/modifier/{id}", name="modifier_sortir")
     */
    public function modifier_sortir(Sortie $sortie, EtatRepository $etatRepo, Request $rq, EntityManagerInterface $emi): Response
    {
        //Contrôle de l'identité de l'organisateur
        $participantConnecte = $this->getUser();
        if ($sortie->getOrganisateur() != $participantConnecte) {
            $this->addFlash(
                'notice',
                'Vous n\'êtes pas autorisé à modifier cette sortie !'
            );
            return $this->redirectToRoute('accueil');
        }

        $form = $this->createForm(ModifierSortieType::class, $sortie);

        $form->handleRequest($rq);


        if ($form->isSubmitted()) {


            $emi->flush();
            return $this->redirectToRoute('accueil');
        }

        $this->addFlash(
            'notice',
            ' Vous avez déjà modifie une sortie'

        );


        return $this->render('sortie/modifiersortie.html.twig', [

            'formular' => $form->createView(),
            'list_sortie' => $sortie

        ]);
    }


    /**
     * @Route("/inscrire/{id}", name="inscrire")
     */
    public function inscrire(Sortie $sortie, EntityManagerInterface $emi): Response
    {

        $participantConnecte = $this->getUser();

        $sortie->addParticipant($participantConnecte);

        $emi->flush();

        $this->addFlash(
            'notice',
            'Vous vous êtes inscrit à une sortie'
        );




        return $this->redirectToRoute('accueil');
    }

    /**
     * @Route("/desister/{id}", name="desister")
     */
    public function desister(Sortie $sortie, EntityManagerInterface $emi): Response
    {

        $participantConnecte = $this->getUser();

        $sortie->removeParticipant($participantConnecte);


        $emi->flush();

        $this->addFlash(
            'notice',
            'Vous vous êtes désisté d une sortie'
        );




        return $this->redirectToRoute('accueil');
    }



    /**
     * @Route("/remove/{id}", name="remove")
     */
    public function remove(Sortie $sortie,EntityManagerInterface $emi): Response
    {
       
        $emi -> remove($sortie);

        $emi -> flush();

        $this->addFlash(
            'notice',
            'Vous avez supprimé une sortie'
        );


        return $this->redirectToRoute('accueil'); 


    }
}
