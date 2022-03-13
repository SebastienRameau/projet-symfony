<?php

namespace App\Repository;

use App\Classes\FiltreSorties;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Sortie $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Sortie $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }


    public function findByFilters(FiltreSorties $filtreSorties, Participant $participantConnecte, $dateJour)
    {
        //Pour simplifier l'écriture, on hydrate des variables avec les paramètres du formulaire
        $campus = $filtreSorties->getCampus();
        $nom = $filtreSorties->getNom();
        $dateMin = $filtreSorties->getDateMin();
        $dateMax = $filtreSorties->getDateMax();
        $isOrganisateur = $filtreSorties->getIsOrganisateur();
        $isInscrit = $filtreSorties->getIsInscrit();
        $isNonInscrit = $filtreSorties->getIsNonInscrit();
        $isPassee = $filtreSorties->getIsPassee();

        //Création du query builder. s ~= sortie
        $qb = $this->createQueryBuilder("s");
        //Select all
        $qb->select('s');

        //On fait les jointures avec les tables requises (INNER JOIN)
        $qb->join('s.campus', 'c')->addSelect('c')
            ->join('s.participants', 'p')->addSelect('p')
            ->join('s.etat', 'e')->addSelect('e')
            ->join('s.organisateur', 'o')->addSelect('o');

        //Affichage au chargement de la page d'accueil (ou quand le formulaire est vide)
        //On récupère toutes les sorties sauf les Historisées et les Créées
        $qb->andWhere('e.libelle != :historisee')->orWhere('e.libelle != :creee')
            ->setParameter(':historisee', 'Historisée')
            ->setParameter(':creee', 'Créée');

        //On ajoute des conditions si le formulaire a été rempli (éléments not null)
        //Si un campus est choisi, on ne garde parmis les sorties sélectionnées précédemment que celles qui sont associées au campus choisi
        if ($campus != null) {
            $qb->andWhere('s.campus = :campus')
                //Note : si on set des paramètres en dehors des if et qu'on ne les utilise pas ensuite (qu'on ne rentre pas dans le if),
                //on reçoit un message d'erreur du type "Too many parameters". On doit donc les "setter" dans les if.
                ->setParameter(':campus', $campus);
        }
        //Si un nom est tapé, on ne garde que les sorties qui ont un nom contenant celui-ci
        if ($nom != null) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter(':nom', '%' . $nom . '%'); //Les '%' sont nécessaires au fonctionnement du LIKE en SQL.
        }

        //Si on a une date minimum, on ne garde que les sorties dont la date de début est postérieure à celle-ci
        if ($dateMin != null) {
            $qb->andWhere('s.dateHeureDebut >= :dateMin')
                ->setParameter(':dateMin', $dateMin);
        }

        //Si on a une date maximum, on ne garde que les sorties dont la date de début est antérieure à celle-ci
        if ($dateMax != null) {
            $qb->andWhere('s.dateHeureDebut <= :dateMax')
                ->setParameter(':dateMax', $dateMax);
        }

        //Si la checkbox Organisateur a été cochée, on rajoute les sorties dont le participant connecté est l'organisateur
        if ($isOrganisateur != null) {
            $qb->orWhere('s.organisateur = :participantConnecte')
                ->setParameter(':participantConnecte', $participantConnecte);
        }

        //Si les deux checkboxs Inscrit et Non Inscrit ont été cochées, on ne change rien car les deux s'affichent (voir maquette), cependant...
        //Si Inscrit a été cochée mais pas Non Inscrit, on ne garde que les sorties auxquelles le participant connecté est inscrit
        if ($isInscrit != null && $isNonInscrit == null) {
            $qb->andWhere(':participantConnecte MEMBER OF s.participants')
                ->setParameter(':participantConnecte', $participantConnecte);
        }
        //Si Non Inscrit a été cochée mais pas Inscrit, on ne garde que les sorties auxquelles le participant connecté n'est pas inscrit
        if ($isNonInscrit != null && $isInscrit == null) {
            $qb->andWhere(':participantConnecte NOT MEMBER OF s.participants')
                ->setParameter(':participantConnecte', $participantConnecte);
        }

        //Si la checkbox Est Passée a été cochée, on ne garde que les sorties qui sont déjà passées
        if ($isPassee != null) {
            $qb->andWhere('s.dateHeureDebut <= :dateJour')
                ->setParameter(':dateJour', $dateJour);
        }

        //On les affiche par date de la sortie ascendante
        $qb->orderBy('s.dateHeureDebut', 'ASC');

        //On retourne la liste des sorties obtenues
        return $qb->getQuery()->getResult();
    }



    // /**
    //  * @return Sortie[] Returns an array of Sortie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
