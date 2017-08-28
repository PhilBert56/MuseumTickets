<?php

namespace Museum\TicketBundle\Form;
namespace Museum\TicketBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Museum\TicketBundle\Entity\Customer;

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
            }
            return $this->render('MuseumTicketBundle:Museum:recapAndPay.html.twig', [
                'recapAndPayForm' => $form->createView(),
                'tickets'=>$tickets,
                'total' =>$totalAmount
            ]);
        }

        return $this->render('MuseumTicketBundle:Museum:recapAndPay.html.twig',[
            'recapAndPayForm' => $form->createView(),
            'tickets'=>$tickets,
            'total' =>$totalAmount
        ]);

    }

}
