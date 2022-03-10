<?php

namespace App\Controller;

use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/accueil", name="accueil")
     */
    public function accueil(ParticipantRepository $repo): Response
    {
        return $this->render('main/accueil.html.twig', [
            'participants' => $repo->findAll(),
        ]);
    }
}
