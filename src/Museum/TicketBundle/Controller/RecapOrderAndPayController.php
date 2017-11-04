<?php

namespace Museum\TicketBundle\Form;
namespace Museum\TicketBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Museum\TicketBundle\Entity\Customer;
//use Museum\TicketBundle\Entity\Ticket;
//use Museum\TicketBundle\Entity\WorkingDay;

class RecapOrderAndPayController extends Controller
{
    /**
     * @Route("/RecapTickets", name="recapTickets")
     */

    public function recapTicketsAction(Request $request)
    {
        $session = $this->get('session');

        $ticketFolder = $session->get('ticketFolder');
        $tickets = $ticketFolder->getTickets();
        $customer = $session->get('customer');

        /* Si pas de visiteur encore défini, retour sur la définition d'un visiteur */

        if ( count ($tickets) == 0) {
            return $this->redirect( $this->generateUrl('visitor'));
        }

        if (!$customer) $customer = new Customer();
        $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $customer);
        $formBuilder
            ->add('email',      EmailType::class)
        ;
        $form = $formBuilder->getForm();

        $ticketFolder->setTotalAmount();

        $totalAmount = $ticketFolder->getTotalAmount();

        if ($request->isMethod('POST')) {

            $form->handleRequest($request);
            // On vérifie que les valeurs entrées sont correctes
            if ($form->isValid()) {
                $ticketFolder->setCustomer($customer);
                $session->set('ticketOrder', $ticketFolder);
            } else {
              $translator = $this->get('translator');
              $translatedMessage = $translator->trans('flashMessage.typingIncorrect');
              $this->addFlash('error',$translatedMessage );
              return;
            }
/*
            if (!$ticketFolder->paymentAlreadyProcessed) {
              $token = $request->request->get('stripeToken');

              \Stripe\Stripe::setApiKey("sk_test_6t3qfq3AknEGeNqYq8nzGEDs");

              \Stripe\Charge::create(array(
                "amount" => $totalAmount * 100,
                "currency" => "EUR",
                "source" => $token,
                "description" => "Museum tickets"
              ));
*/

              $ticketFolderPaymentService = $this->container->get('museum.payment');
              $isPaymentOK = $ticketFolderPaymentService->processPayment($request, $ticketFolder);

              // si paiement OK
              if ($isPaymentOK){

                // persistence des donnée
                $dataPersistenceService = $this->container->get('museum.dataPersistence');
                $dataPersistenceService->storeData();

                // envoi des emails pour les billets
                $mailerUser = $this->container->getParameter('mailer_user');
                $mailer = $this->get('mailer');
                $ticketFolder->confirmTicketsByEmail($mailerUser, $mailer);
                $ticketFolder->setPaymentAlreadyProcessed(true);
                $translator = $this->get('translator');
                $translatedMessage = $translator->trans('flashMessage.orderSuccess');
                $this->addFlash('success', $translatedMessage.' '.$customer->getEmail());
              } else {
                $translator = $this->get('translator');
                $translatedMessage = $translator->trans('flashMessage.orderFailure');
                $this->addFlash('error', $translatedMessage);
              }


            return $this->render('MuseumTicketBundle:Museum:finalView.html.twig', [
                //'recapAndPayForm' => $form->createView(),
                'tickets'=>$tickets,
                'total' =>$totalAmount
            ]);
        }

        $locale = $request->getLocale();
        return $this->render('MuseumTicketBundle:Museum:recapAndPay.html.twig',[
            'recapAndPayForm' => $form->createView(),
            'tickets'=>$tickets,
            'total' =>$totalAmount,
            'locale' => $locale
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

        foreach ($tickets as $ticket) {
            //$em->persist($ticket);

            $visitor = $ticket->getVisitor();

            /* génération du code sur le ticket - solution temporaire */

            $code = '987654321';

            $ticket->setTicketCode($code);

            $ticket->setCustomer($customer);

            $em->persist($visitor);
            $em->persist($ticket);

            $this->refreshNumberOfVisitorPerDay($ticket->getDateOfVisit());
        }

        $em->flush();

        return $this->render('MuseumTicketBundle:Museum:finalView.html.twig');

    }
/*
    public function sendEmail($ticket,$customer) {


            $eMailAdress = $customer->getEmail();
            $eMailAdressPourTest = 'phil-bert@club-internet.fr';

            $visitor = $ticket->getVisitor();

            $ticketDescription = 'VISITEUR : ';
            $ticketDescription = $ticketDescription.$visitor->getFirstName();
            $ticketDescription = $ticketDescription.' '.$visitor->getName();
            $birthDate = $visitor->getbirthDate()->format('j-n-Y');
            $ticketDescription = $ticketDescription.' '.$birthDate;
            $ticketDescription = $ticketDescription.' '.$ticket->getPrice().' €';

            $message = \Swift_Message::newInstance()
                ->setSubject('Billetterie')
                ->setFrom($this->container->getParameter('mailer_user'))
                ->setTo($eMailAdressPourTest)
                ->setBody($ticketDescription);

            $this->get('mailer')->send($message);

            $this->addFlash('OK ', 'billet envoyé');


    }

*/
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
