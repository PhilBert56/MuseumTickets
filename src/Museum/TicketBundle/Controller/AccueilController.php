<?php

namespace Museum\TicketBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class AccueilController extends Controller
{
/**
 * @Route("/accueil", name="accueil")
*/
  public function accueilAction()
  {
    return $this->render('MuseumTicketBundle:Museum:accueil.html.twig');
  }
}
