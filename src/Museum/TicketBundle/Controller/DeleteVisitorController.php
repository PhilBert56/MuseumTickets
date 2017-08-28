<?php

namespace Museum\TicketBundle\Form;
namespace Museum\TicketBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Museum\TicketBundle\Entity\Customer;

class DeleteVisitorController extends Controller
{

    /**
     * @Route("/DeleteVisitor/", name = "deleteVisitor")
     */
    public function deleteVisitorAction()
    {

        $session = $this->get('session');
        $ticketFolder = $session->get('ticketFolder');

        $customer = $ticketFolder->getCustomer();

        $request = Request::createFromGlobals();
        $firstName = $request->query->get('firstName');
        $lastName = $request->query->get('lastName');

        $ticketFolder->cancelVisitorAndAssociatedTicket($firstName, $lastName);
        $totalAmount = $ticketFolder->getTotalAmount();
        $tickets = $ticketFolder->getTickets();

        if ( count ($tickets) == 0) {
            return $this->redirect( $this->generateUrl('visitor'));
        }

        // tester si le Customer existe sinon le creer
        if (!$customer) $customer = new Customer();

        $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $customer);

        $formBuilder
            ->add('email', EmailType::class)
        ;
        $form = $formBuilder->getForm();

        return $this->render('MuseumTicketBundle:Museum:recapAndPay.html.twig',[
            'recapAndPayForm' => $form->createView(),
            'tickets'=>$tickets,
            'total' => $totalAmount
        ]);
    }
}
