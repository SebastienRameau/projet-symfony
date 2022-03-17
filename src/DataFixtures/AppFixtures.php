<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use DateInterval;
use DateTime;
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

        // $villes = $this->addVilles();

        // $lieux = $this->addLieux($villes);

        // $participants = $this->addParticipants($campusTab);

        $this->addPourPresentation();
        // $this->addSorties($campusTab, $lieux, $participants);
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
            $dateDebut->modify('+6 months');
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

            $duree30 = $faker->numberBetween(30,30);
                    
            if ($dateDebut < $dateNow && $interval->format('%R%a') > $duree30 ) {
                
                $sortie->setEtat($this->manager->getRepository(Etat::class)->findOneBy(['libelle' => 'Historisée']));

            }else if ($dateDebut < $dateNow && $interval->format('%i') < $duree) {
                $sortie->setEtat($this->manager->getRepository(Etat::class)->findOneBy(['libelle' => 'Activité en cours']));


            }else if ($dateDebut < $dateNow && $interval->format('%i') >= $duree) {
                $sortie->setEtat($this->manager->getRepository(Etat::class)->findOneBy(['libelle' => 'Passée']));

            }else if ($nbInscrits == $nbInscriptionMax || $dateLimiteInscription < $dateNow && $interval->format('%R%a') < $duree30  ) {

                $sortie->setEtat($this->manager->getRepository(Etat::class)->findOneBy(['libelle' => 'Cloturée']));    

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

    //Ajout de données personnalisées pour la présentation du projet.

    public function addPourPresentation(){


        //Ajout de quelques villes

        $ville1 = new Ville();
        $ville1->setNom('Rennes')->setCodePostal('35000');
        $this->manager->persist($ville1);

        $ville2 = new Ville();
        $ville2->setNom('Chartres-de-Bretagne')->setCodePostal('35131');
        $this->manager->persist($ville2);

        $ville3 = new Ville();
        $ville3->setNom('Saint-Jacques-de-la-Lande')->setCodePostal('35136');
        $this->manager->persist($ville3);
        
        $this->manager->flush();


        //Ajout de quelques lieux (latitudes et longitudes au hasard ^^)

        $lieu1 = new Lieu();
        $lieu1->setNom('Speakeasy Bar')->setVille($this->manager->getRepository(Ville::class)->findOneBy(['nom' => 'Rennes']))
            ->setRue('32 Rue Jean Marie Duhamel')->setLatitude('-81.464081')->setLongitude('-17.712601');
        $this->manager->persist($lieu1);

        $lieu2 = new Lieu();
        $lieu2->setNom('Melody Nelson')->setVille($this->manager->getRepository(Ville::class)->findOneBy(['nom' => 'Rennes']))
            ->setRue('4 Rue Saint-Thomas')->setLatitude('-81.464082')->setLongitude('-17.712602');
        $this->manager->persist($lieu2);

        $lieu3 = new Lieu();
        $lieu3->setNom('Bar de l\'Entracte')->setVille($this->manager->getRepository(Ville::class)->findOneBy(['nom' => 'Chartres-de-Bretagne']))
            ->setRue('66 Av. du Général de Gaulle')->setLatitude('-81.464083')->setLongitude('-17.712603');
        $this->manager->persist($lieu3);

        $lieu4 = new Lieu();
        $lieu4->setNom('Le Central Bar')->setVille($this->manager->getRepository(Ville::class)->findOneBy(['nom' => 'Chartres-de-Bretagne']))
            ->setRue('2 Pl. René Cassin')->setLatitude('-81.464084')->setLongitude('-17.712604');
        $this->manager->persist($lieu4);

        $lieu5 = new Lieu();
        $lieu5->setNom('Bar de l\'Aviation')->setVille($this->manager->getRepository(Ville::class)->findOneBy(['nom' => 'Saint-Jacques-de-la-Lande']))
            ->setRue('4 Rue Jules Vallès')->setLatitude('-81.464085')->setLongitude('-17.712605');
        $this->manager->persist($lieu5);

        $lieu6 = new Lieu();
        $lieu6->setNom('Brasserie du Vieux Singe')->setVille($this->manager->getRepository(Ville::class)->findOneBy(['nom' => 'Saint-Jacques-de-la-Lande']))
            ->setRue('4 bis Rue Janig Corlay')->setLatitude('-81.464086')->setLongitude('-17.712606');
        $this->manager->persist($lieu6);
        
        $this->manager->flush();


        //Ajout de quelques participants

        $p1 = new Participant();
        $p1->setNom('Rameau')->setPrenom('Sébastien')->setPseudo('Seb')->setTelephone('0612345789')->setMail('seb.rameau@google.com')
            ->setActif(true)->setCampus($this->manager->getRepository(Campus::class)->findOneBy(['nom' => 'Chartres de Bretagne']))
            ->setPassword($this->hasher->hashPassword($p1, '123'));
        $this->manager->persist($p1);

        $p2 = new Participant();
        $p2->setNom('Durant')->setPrenom('Nathan')->setPseudo('Natou')->setTelephone('1654987325')->setMail('nat.durant@google.com')
            ->setActif(true)->setCampus($this->manager->getRepository(Campus::class)->findOneBy(['nom' => 'Chartres de Bretagne']))
            ->setPassword($this->hasher->hashPassword($p2, '123'));
        $this->manager->persist($p2);

        $p3 = new Participant();
        $p3->setNom('Dupond')->setPrenom('Raymond')->setPseudo('rayray')->setTelephone('0216549837')->setMail('ray.dupond@google.com')
            ->setActif(true)->setCampus($this->manager->getRepository(Campus::class)->findOneBy(['nom' => 'Saint Herblain']))
            ->setPassword($this->hasher->hashPassword($p3, '123'));
        $this->manager->persist($p3);

        $p4 = new Participant();
        $p4->setNom('Duval')->setPrenom('Chantale')->setPseudo('Chaton')->setTelephone('0715326498')->setMail('cha.duval@google.com')
            ->setActif(true)->setCampus($this->manager->getRepository(Campus::class)->findOneBy(['nom' => 'Saint Herblain']))
            ->setPassword($this->hasher->hashPassword($p4, '123'));
        $this->manager->persist($p4);

        $p5 = new Participant();
        $p5->setNom('Calin')->setPrenom('Jean-Michel')->setPseudo('Doudou')->setTelephone('0612345789')->setMail('jean.calin@google.com')
            ->setActif(true)->setCampus($this->manager->getRepository(Campus::class)->findOneBy(['nom' => 'La Roche sur Yon']))
            ->setPassword($this->hasher->hashPassword($p5, '123'));
        $this->manager->persist($p5);

        $this->manager->flush();


        //Ajout de quelques sorties

        $s1 = new Sortie();
        $s1->setNom('Tous au bar pour Noël !')->setDateHeureDebut(new DateTime('2021-12-23 19:00:00'))->setDuree(60)
            ->setDateLimiteInscription(new DateTime('2021-12-22'))->setNbInscriptionMax(6)
            ->setInfosSortie('Ceci est une sortie test pour la présentation du projet devant le groupe')
            ->setOrganisateur($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Jean-Michel']))
            ->setCampus($this->manager->getRepository(Campus::class)->findOneBy(['nom' => 'La Roche sur Yon']))
            ->setEtat($this->manager->getRepository(Etat::class)->findOneBy(['libelle' => 'Historisée']))
            ->setLieu($this->manager->getRepository(Lieu::class)->findOneBy(['nom' => 'Brasserie du Vieux Singe']));
        $s1->addParticipant($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Jean-Michel']));
        $s1->addParticipant($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Chantale']));
        $this->manager->persist($s1);
        
        $s2 = new Sortie();
        $s2->setNom('Sortie au bar')->setDateHeureDebut(new DateTime('2022-03-23 17:30:00'))->setDuree(90)
            ->setDateLimiteInscription(new DateTime('2022-03-23'))->setNbInscriptionMax(12)
            ->setInfosSortie('Ceci est une sortie test pour la présentation du projet devant le groupe')
            ->setOrganisateur($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Sébastien']))
            ->setCampus($this->manager->getRepository(Campus::class)->findOneBy(['nom' => 'Chartres de Bretagne']))
            ->setEtat($this->manager->getRepository(Etat::class)->findOneBy(['libelle' => 'Créée']))
            ->setLieu($this->manager->getRepository(Lieu::class)->findOneBy(['nom' => 'Bar de l\'Entracte']));
        $s2->addParticipant($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Sébastien']));
        $this->manager->persist($s2);

        $s3 = new Sortie();
        $s3->setNom('Viens boire un p\'tit coup à la maison')->setDateHeureDebut(new DateTime('2022-03-21 22:00:00'))->setDuree(120)
            ->setDateLimiteInscription(new DateTime('2022-03-20'))->setNbInscriptionMax(3)
            ->setInfosSortie('Ceci est une sortie test pour la présentation du projet devant le groupe')
            ->setOrganisateur($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Sébastien']))
            ->setCampus($this->manager->getRepository(Campus::class)->findOneBy(['nom' => 'Chartres de Bretagne']))
            ->setEtat($this->manager->getRepository(Etat::class)->findOneBy(['libelle' => 'Ouverte']))
            ->setLieu($this->manager->getRepository(Lieu::class)->findOneBy(['nom' => 'Speakeasy Bar']));
        $s3->addParticipant($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Sébastien']));
        $s3->addParticipant($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Raymond']));
        $this->manager->persist($s3);

        $s4 = new Sortie();
        $s4->setNom('Sortie au bar après le projet en petit commité')->setDateHeureDebut(new DateTime('2022-03-18 18:00:00'))->setDuree(120)
            ->setDateLimiteInscription(new DateTime('2022-03-18'))->setNbInscriptionMax(4)
            ->setInfosSortie('Ceci est une sortie test pour la présentation du projet devant le groupe')
            ->setOrganisateur($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Nathan']))
            ->setCampus($this->manager->getRepository(Campus::class)->findOneBy(['nom' => 'Chartres de Bretagne']))
            ->setEtat($this->manager->getRepository(Etat::class)->findOneBy(['libelle' => 'Cloturée']))
            ->setLieu($this->manager->getRepository(Lieu::class)->findOneBy(['nom' => 'Le Central Bar']));
        $s4->addParticipant($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Nathan']));
        $s4->addParticipant($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Sébastien']));
        $s4->addParticipant($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Chantale']));
        $s4->addParticipant($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Jean-Michel']));
        $this->manager->persist($s4);

        $s5 = new Sortie();
        $s5->setNom('Pendant la présentation du projet ? Bizarre...')->setDateHeureDebut(new DateTime('2022-03-17 14:00:00'))->setDuree(180)
            ->setDateLimiteInscription(new DateTime('2022-03-17'))->setNbInscriptionMax(3)
            ->setInfosSortie('Ceci est une sortie test pour la présentation du projet devant le groupe')
            ->setOrganisateur($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Sébastien']))
            ->setCampus($this->manager->getRepository(Campus::class)->findOneBy(['nom' => 'Chartres de Bretagne']))
            ->setEtat($this->manager->getRepository(Etat::class)->findOneBy(['libelle' => 'Activité en cours']))
            ->setLieu($this->manager->getRepository(Lieu::class)->findOneBy(['nom' => 'Melody Nelson']));
        $s5->addParticipant($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Sébastien']));
        $s5->addParticipant($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Raymond']));
        $s5->addParticipant($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Nathan']));
        $this->manager->persist($s5);

        $s6 = new Sortie();
        $s6->setNom('Au bar le vendredi 13 Mai')->setDateHeureDebut(new DateTime('2022-05-13 17:00:00'))->setDuree(180)
            ->setDateLimiteInscription(new DateTime('2022-05-07'))->setNbInscriptionMax(20)
            ->setInfosSortie('Ceci est une sortie test pour la présentation du projet devant le groupe')
            ->setOrganisateur($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Jean-Michel']))
            ->setCampus($this->manager->getRepository(Campus::class)->findOneBy(['nom' => 'La Roche sur Yon']))
            ->setEtat($this->manager->getRepository(Etat::class)->findOneBy(['libelle' => 'Annulée']))
            ->setLieu($this->manager->getRepository(Lieu::class)->findOneBy(['nom' => 'Bar de l\'Aviation']));
        $s6->addParticipant($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Jean-Michel']));
        $s6->addParticipant($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Raymond']));
        $this->manager->persist($s6);

        $s7 = new Sortie();
        $s7->setNom('Finissons tous ronds !')->setDateHeureDebut(new DateTime('2022-03-12 17:30:00'))->setDuree(120)
            ->setDateLimiteInscription(new DateTime('2022-03-11'))->setNbInscriptionMax(15)
            ->setInfosSortie('Ceci est une sortie test pour la présentation du projet devant le groupe')
            ->setOrganisateur($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Chantale']))
            ->setCampus($this->manager->getRepository(Campus::class)->findOneBy(['nom' => 'Saint Herblain']))
            ->setEtat($this->manager->getRepository(Etat::class)->findOneBy(['libelle' => 'Passée']))
            ->setLieu($this->manager->getRepository(Lieu::class)->findOneBy(['nom' => 'Bar de l\'Entracte']));
        $s7->addParticipant($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Chantale']));
        $s7->addParticipant($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Raymond']));
        $s7->addParticipant($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Nathan']));
        $s7->addParticipant($this->manager->getRepository(Participant::class)->findOneBy(['prenom' => 'Jean-Michel']));
        $this->manager->persist($s7);

        $this->manager->flush();

    }
}
