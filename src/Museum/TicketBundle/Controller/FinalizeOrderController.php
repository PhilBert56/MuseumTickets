<?php

namespace Museum\TicketBundle\Form;
namespace Museum\TicketBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Museum\TicketBundle\Entity\WorkingDay;
use Museum\TicketBundle\TicketFolder\TicketFolder;
//use TicketBundle\Form\FormTypeCustomer;


//require_once 'C:/PHILIPPE/OpenClass/P4/BilletterieMusee/billets1/vendor/autoload.php';

class FinalizeOrderController extends Controller
{
    /**
     * @Route("/FinalizeOrder", name="finalizeOrder")
     */

     public function checkoutAction(Request $request){

       $session = $this->get('session');
       $ticketFolder = $session->get('ticketFolder');
       $totalAmount = $ticketFolder->gettotalAmount();
       $customer = $ticketFolder->getCustomer();

       return $this->render('MuseumTicketBundle:Museum:checkout.html.twig' , [
           'total' =>$totalAmount,
           'stripe_public_key' => $this->getParameter('stripe_public_key')
       ]);

     }


    public function storeData(Request $request)
    {

        $session = $this->get('session');
        $ticketFolder = $session->get('ticketFolder');
        $tickets = $ticketFolder->getTickets() ;
        $customer = $ticketFolder->getCustomer();

        $customer->setTotalAmount($ticketFolder->getTotalAmount());

        $em = $this->getDoctrine()->getManager();

        $em->persist($customer);

        $this->storeTicketsAndAssociatedVisitors($em,$tickets,$customer);
/*
        foreach ($tickets as $ticket) {
            //$em->persist($ticket);

            $visitor = $ticket->getVisitor();

            /* génération du code sur le ticket - solution temporaire */

/*            $code = '1234';
            $ticket->setTicketCode($code);

            $ticket->setCustomer($customer);

            $em->persist($visitor);
            $em->persist($ticket);

            $this->sendEmail($ticket, $customer);

            $this->refreshNumberOfVisitorPerDay($ticket->getDate)
        }
*/


        $em->flush();


        return $this->render('MuseumTicketBundle:Museum:finalView.html.twig');

    }

    public function sendEmail($visitors, $customer) {

            $message = \Swift_Message::newInstance()
                ->setSubject('test')
                ->setFrom($this->container->getParameter('mailer_username'))
                ->setTo('phil-bert@club-internet.fr')
                ->setBody('coucou');

            $this->get('mailer')->send($message);
            dump($message);
            $this->addFlash('success', 'coucou envoyé');


    }


    public function storeTicketsAndAssociatedVisitors($em, $tickets, $customer){

        foreach ($tickets as $ticket) {

            $visitor = $ticket->getVisitor();

            /* génération du code sur le ticket - solution temporaire */
            $code = $this->encodeTicket($ticket);
            $ticket->setTicketCode($code);

            $ticket->setCustomer($customer);

            $em->persist($visitor);
            $em->persist($ticket);

            $this->refreshNumberOfVisitorPerDay($ticket->getDateOfVisit());
            $this->sendEmail($ticket, $customer);


        }

    }

    public function encodeTicket($ticket){

        return '123456';


    }

    public function refreshNumberOfVisitorPerDay($date){

        $em = $this->getDoctrine()->getManager();
        $workingDay = $em->getRepository('MuseumTicketBundle:WorkingDay')
            ->findOneByDate( $date );

        if(!$workingDay){
            $workingDay = new WorkingDay();
            $workingDay->setDate($date);
            $numberOfVisitors = 0;
        } else {
            $numberOfVisitors = $workingDay->getNumberOfVisitors();
        }
        $numberOfVisitors++;
        $workingDay->setNumberOfVisitors($numberOfVisitors);

        $em->persist($workingDay);
        $em->flush();
    }




}
