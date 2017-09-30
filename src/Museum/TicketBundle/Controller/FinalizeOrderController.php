<?php

namespace Museum\TicketBundle\Form;
namespace Museum\TicketBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class FinalizeOrderController extends Controller
{
    /**
     * @Route("/FinalizeOrder", name="finalizeOrder")
     */

     public function checkoutAction(Request $request){

       $totalAmount = $this
        ->get('session')
        ->get('ticketFolder')
        ->gettotalAmount();


       return $this->render('MuseumTicketBundle:Museum:checkout.html.twig' , [
           'total' =>$totalAmount,
           'stripe_public_key' => $this->getParameter('stripe_public_key')
       ]);

     }




}
