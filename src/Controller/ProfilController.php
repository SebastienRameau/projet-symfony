<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    /**
     * @Route("/monprofil", name="app_profil")
     */
    public function profil(): Response
    {
        return $this->render('profil/monprofil.html.twig', [
            'controller_name' => 'ProfilController',
        ]);
    }
}
