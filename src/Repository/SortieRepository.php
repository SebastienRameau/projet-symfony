<?php

namespace App\Repository;

use App\Classes\FiltreSorties as ClassesFiltreSorties;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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


    public function findByFilters(ClassesFiltreSorties $filtreSorties, EtatRepository $repoEtat, CampusRepository $repoCampus,
     Participant $participantConnecte)
    {

        $qb = $this->createQueryBuilder("s");

        $qb->select('s');

        if ($filtreSorties) {
            
            $campusNom = $filtreSorties->getCampusNom();
            $nom = $filtreSorties->getNom();
            $dateMin = $filtreSorties->getDateMin();
            $dateMax = $filtreSorties->getDateMax();
            $isOrganisateur = $filtreSorties->getIsOrganisateur();
            $isInscrit = $filtreSorties->getIsInscrit();
            $isNonInscrit = $filtreSorties->getIsNonInscrit();
            $isPassee = $filtreSorties->getIsPassee();

            if ($campusNom) {
                $qb->andWhere('s.campus = '.$repoCampus->findOneBy(['nom' => $campusNom])->getId());
            }
            if ($nom) {
                $qb->andWhere('s.nom like '.$nom);
            }



        }else{
            $qb->where('s.etat = '.$repoEtat->findOneBy(['libelle' => 'Ouverte'])->getId())
            ->orWhere('s.etat = '.$repoEtat->findOneBy(['libelle' => 'Cloturée'])->getId())
            ->orWhere('s.etat = '.$repoEtat->findOneBy(['libelle' => 'Activité en cours'])->getId())
            ->orWhere('s.etat = '.$repoEtat->findOneBy(['libelle' => 'Passée'])->getId())
            ->orWhere('s.etat = '.$repoEtat->findOneBy(['libelle' => 'Annulée'])->getId())
            ->orWhere('s.organisateur = '.$participantConnecte->getId());
        }
        $qb->orderBy('s.dateHeureDebut', 'ASC');

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
