<?php

namespace App\Controller;

use App\Entity\Participant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    /**

     * @Route("/profil/{id}", name="app_profil")
     */
    public function afficherProfil(Participant $participant): Response
    {

        return $this->render('profil/index.html.twig', [

            'list_participant' => $participant,

        ]);
    }
}
