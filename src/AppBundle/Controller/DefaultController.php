<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Museum\TicketBundle\TemporaryObjects\AgeLimits;
use Museum\TicketBundle\TemporaryObjects\Prices;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need

        $ages = new AgeLimits();
        $prices = new Prices();

        return $this->render('MuseumTicketBundle:museum:accueil.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'ages' => $ages,
            'prices' => $prices
        ]);
    }
}
