<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\AnnulerFormType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
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
    public function accueil(CampusRepository $repoCampus, SortieRepository $repoSortie, EtatRepository $repoEtat,
         ParticipantRepository $repoParticipant): Response
    {
        //Envoyer la date du jour
        $date = explode("/",date('d/m/Y'));
        list($day,$month,$year) = $date;
        $dateJour = $day.'/'.$month.'/'.$year;

        
        //Envoyer le participant connecté (Voir plus tard, quand Estelle aura fait la connexion)
        $participantConnecte = $repoParticipant->findOneBy(['id' => '31']); //temporaire


        //Envoyer la liste des campus
        $campusListe = $repoCampus->findAll();


        //Envoyer la liste des sorties Sauf historisées Ou non publiées (créées) (sauf si le participant est l'organisateur)
        $requete = $repoSortie->createQueryBuilder('p')
            ->select('p')
            ->where('p.etat = '.$repoEtat->findOneBy(['libelle' => 'Ouverte'])->getId())
            ->orWhere('p.etat = '.$repoEtat->findOneBy(['libelle' => 'Cloturée'])->getId())
            ->orWhere('p.etat = '.$repoEtat->findOneBy(['libelle' => 'Activité en cours'])->getId())
            ->orWhere('p.etat = '.$repoEtat->findOneBy(['libelle' => 'Passée'])->getId())
            ->orWhere('p.etat = '.$repoEtat->findOneBy(['libelle' => 'Annulée'])->getId())
            ->orWhere('p.organisateur = '.$participantConnecte->getId())
            ->orderBy('p.dateHeureDebut', 'ASC')
            ->getQuery();

        $sortiesListe = $requete->getResult();


        //Gérer le bouton Rechercher
        //Ou tout faire en Javascript ?


        //Gérer le bouton Créer une sortie
        //Non : rediriger vers la méthode de création au click sur le bouton


        return $this->render('sortie/accueil.html.twig', [
            'date_jour' => $dateJour,
            'participant_connecte' => $participantConnecte,
            'campus_liste' => $campusListe,
            'sorties_liste' => $sortiesListe,
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

            $time = date('H:i:s \O\n d/m/Y');
            $dateDebut = $sortie->getDateHeureDebut();
            
            if($time > $dateDebut){

                $etat=$etatRepo->findOneBy(['libelle'=> 'Annulée']);
                $sortie->setEtat($etat);

                $emi->flush();
                return $this->redirectToRoute('accueil');

            }
            
            $this->addFlash(
                'notice',
                ' Vous ne pouvez pas annuler cette sortie parce que celle-ci a deja commencée'

            );

        }

        return $this->render('sortie/annuler.html.twig', [
            'formular'=> $form->createView()
        ]);
    }

}
