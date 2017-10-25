<?php

namespace Museum\TicketBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Museum\TicketBundle\TemporaryObjects\AgeLimits;
use Museum\TicketBundle\TemporaryObjects\Prices;

class AccueilController extends Controller
{
/**
 * @Route("/accueil", name="accueil")
*/
  public function accueilAction()
  {
    $ages = new AgeLimits();
    $prices = new Prices();

    return $this->render('MuseumTicketBundle:Museum:accueil.html.twig', [
      'ages' => $ages,
      'prices' => $prices
    ]);
  }
}
