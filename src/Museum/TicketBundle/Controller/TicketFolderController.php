<?php
namespace Museum\TicketBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
class TicketFolderController extends Controller
{
    /**
    * @Route("/OrderTickets", name="ordertickets")
    */
    public function orderTicketsAction(){
      return $this->render('MuseumTicketBundle:Museum:ordertickets.html.twig');
    }
}
