<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Workshop;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends Controller
{
    public function indexAction(Request $request)
    {
        $workshops = $this->getDoctrine()
            ->getRepository(Workshop::class)
            ->calendar()
        ;

        return $this->render('home/index.html.twig', [
            'workshops' => $workshops
        ]);
    }
}
