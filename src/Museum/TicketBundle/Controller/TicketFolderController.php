<?php
namespace Museum\TicketBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Museum\TicketBundle\TemporaryObjects\TicketFolder;
class TicketFolderController extends Controller
{
    /**
    * @Route("/OrderTickets", name="ordertickets")
    */
    public function orderTicketsAction(Request $request){

      $ticketFolder = $this->getTicketFolder();
      $form = $this->createFormBuilder($ticketFolder)->getForm();

      if ($request->isMethod('POST')) {

        $form->handleRequest($request);
        return $this->redirectToRoute('visitor');

      }
      return $this->render('MuseumTicketBundle:Museum:ordertickets.html.twig', [
            'orderForm' => $form->createView(),
            'message1' => '',
            'message2' => '',
        ]);
    }

    public function getTicketFolder()
    {
        $session = $this->get('session');
        $ticketFolder = new TicketFolder($session);
        return $ticketFolder;
    }
}
