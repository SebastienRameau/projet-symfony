<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use DateInterval;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\DBAL\Driver\IBMDB2\Exception\Factory;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private ObjectManager $manager;
    private UserPasswordHasherInterface $hasher;

    // php bin/console doctrine:fixtures:load

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;


        $campusTab = $this->addCampus();

        $this->addEtats();

        $villes = $this->addVilles();

        $lieux = $this->addLieux($villes);

        $participants = $this->addParticipants($campusTab);

        $this->addSorties($campusTab, $lieux, $participants);
    }

    public function __construct(UserPasswordHasherInterface $passwordHasher)

    {

        $this->hasher = $passwordHasher;
    }


    public function addParticipants($campusTab)
    {

        $faker = FakerFactory::create('fr_FR');

        for ($i = 0; $i < 30; $i++) {
            $participant = new Participant();
            $participant->setPrenom($faker->firstName)
                ->setNom($faker->lastName)
                ->setPseudo($faker->userName)
                ->setTelephone($faker->e164PhoneNumber)
                ->setMail($faker->email)
                ->setActif(true)
                ->setCampus($faker->randomElement($campusTab))
                ->setPassword($this->hasher->hashPassword($participant, '123'));

            $this->manager->persist($participant);
        }

        $this->manager->flush();

        $participants = $this->manager->getRepository(Participant::class)->findAll();

        return $participants;
    }


    public function addCampus()
    {

        $campus1 = new Campus();
        $campus1->setNom("Saint Herblain");
        $this->manager->persist($campus1);

        $campus2 = new Campus();
        $campus2->setNom("Chartres de Bretagne");
        $this->manager->persist($campus2);

        $campus3 = new Campus();
        $campus3->setNom("La Roche sur Yon");
        $this->manager->persist($campus3);

        $this->manager->flush();

        $campus = $this->manager->getRepository(Campus::class)->findAll();

        return $campus;
    }


    public function addEtats()
    {

        $etat1 = new Etat();
        $etat1->setLibelle("Créée");
        $this->manager->persist($etat1);

        $etat2 = new Etat();
        $etat2->setLibelle("Ouverte");
        $this->manager->persist($etat2);

        $etat3 = new Etat();
        $etat3->setLibelle("Cloturée");
        $this->manager->persist($etat3);

        $etat4 = new Etat();
        $etat4->setLibelle("Activité en cours");
        $this->manager->persist($etat4);

        $etat5 = new Etat();
        $etat5->setLibelle("Passée");
        $this->manager->persist($etat5);

        $etat6 = new Etat();
        $etat6->setLibelle("Annulée");
        $this->manager->persist($etat6);

        $etat7 = new Etat();
        $etat7->setLibelle("Historisée");
        $this->manager->persist($etat7);

        $this->manager->flush();

    }


    public function addVilles()
    {

        $faker = FakerFactory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $ville = new Ville();
            $ville->setNom($faker->city);
            $ville->setCodePostal($faker->postcode);
            $villes[] = $ville;
            $this->manager->persist($ville);
        }
        $this->manager->flush();

        $villes = $this->manager->getRepository(Ville::class)->findAll();

        return $villes;
    }


    public function addLieux($villes){

        $faker = FakerFactory::create('fr_FR');

        for ($i=0; $i < 12; $i++) { 
            $lieu = new Lieu();
            $lieu->setNom($faker->company);
            $lieu->setRue($faker->streetName);
            $lieu->setLatitude($faker->latitude);
            $lieu->setLongitude($faker->longitude);
            $lieu->setVille($faker->randomElement($villes));
            $this->manager->persist($lieu);
        }
        $this->manager->flush();

        $lieux = $this->manager->getRepository(Lieu::class)->findAll();

        return $lieux;
    }


    public function addSorties($campusTab, $lieux, $participants){

        $faker = FakerFactory::create('fr_FR');

        for ($i=0; $i < 40; $i++) { 
            $sortie = new Sortie();
            $sortie->setNom($faker->text(50));

            $dateDebut = $faker->dateTimeThisYear('now', 'Europe/Paris');
            $sortie->setDateHeureDebut($dateDebut);

            $duree = $faker->numberBetween(30, 300);
            $sortie->setDuree($duree);

            $septJours = clone $dateDebut;
            $septJours->modify('-7 days');
            $dateLimiteInscription = $faker->dateTimeBetween($septJours, $dateDebut);
            $sortie->setDateLimiteInscription($dateLimiteInscription);

            $nbInscriptionMax = $faker->numberBetween(2, 50);
            $sortie->setNbInscriptionMax($nbInscriptionMax);

            $sortie->setInfosSortie($faker->text(300));

            $organisateur = $faker->randomElement($participants);
            $sortie->setOrganisateur($organisateur);

            $sortie->setCampus($faker->randomElement($campusTab));
            $sortie->setLieu($faker->randomElement($lieux));

            $nbInscrits = $faker->numberBetween(1, $nbInscriptionMax);

            $dateNow = date_create(date('d-m-Y H:i:s'));
            $interval = date_diff($dateDebut, $dateNow);
            
            if ($nbInscrits = $nbInscriptionMax or $dateLimiteInscription < $dateNow) {
                $sortie->setEtat($this->manager->getRepository(Etat::class)->findOneBy(['libelle' => 'Cloturée']));
            }elseif ($dateDebut < $dateNow and $interval->format('%a') > 30) {
                $sortie->setEtat($this->manager->getRepository(Etat::class)->findOneBy(['libelle' => 'Historisée']));
            }elseif ($dateDebut < $dateNow and $interval->format('%i') >= $duree) {
                $sortie->setEtat($this->manager->getRepository(Etat::class)->findOneBy(['libelle' => 'Passée']));
            }elseif ($dateDebut < $dateNow and $interval->format('%i') < $duree) {
                $sortie->setEtat($this->manager->getRepository(Etat::class)->findOneBy(['libelle' => 'Activité en cours']));
            }else{
                $sortie->setEtat($this->manager->getRepository(Etat::class)->findOneBy(['libelle' => 'Ouverte']));
            }
                        
            $sortie->addParticipant($organisateur);
            for ($j=0; $j < $nbInscrits-1; $j++) {
                $sortie->addParticipant($faker->randomElement($participants));
            }

            $this->manager->persist($sortie);
        }
        $this->manager->flush();

    }
}
