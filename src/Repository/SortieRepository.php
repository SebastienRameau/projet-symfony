<?php

namespace App\Repository;

use App\Classes\FiltreSorties as ClassesFiltreSorties;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use FiltreSorties;

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


    public function findByFilters(ClassesFiltreSorties $filtreSorties, Participant $participantConnecte)
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

        //On fait les jointures avec les tables requises
        $qb->join('s.campus', 'c')->addSelect('c');
        $qb->join('s.participants', 'p')->addSelect('p');
        $qb->join('s.etat', 'e')->addSelect('e');
        $qb->join('s.organisateur', 'o')->addSelect('o');
        

        //Affichage au chargement de la page d'accueil (ou quand le formulaire est vide)
        //On récupère d'abord toutes les sorties sauf les Historisées et les Créées
        $qb->andWhere('e.libelle != :historisee')->orWhere('e.libelle != :creee')
            ->setParameter(':historisee', 'Historisée')
            ->setParameter(':creee', 'Créée');
        //On rajoute les sorties organisées par l'utilisateur
        $qb->orWhere('s.organisateur = :user')
            ->setParameter(':user', $participantConnecte);


        // //On ajoute des conditions si le formulaire a été rempli (éléments not null)
        //Si un campus est choisi, on ne garde parmis les sorties sélectionnées précédemment que celles qui sont associées au campus choisi
        if ($campus != null) {
            $qb->andWhere('s.campus = :campus')
                ->setParameter(':campus', $campus);
        }
        //Si un nom est tapé, on ne garde que les sorties qui ont un nom contenant celui-ci
        if ($nom != null) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter(':nom', '%'.$nom.'%');
        }

        //Si on a une date minimum, on ne garde que les sorties dont la date de début est postérieure à celle-ci
        // if ($dateMin !=null) {
        //     $qb->andWhere('s.dateHeureDebut >= :dateMin') //NE MARCHE PAS : PROBLEME DATE ?
        //         ->setParameter(':datemin', $dateMin);
        // }





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
