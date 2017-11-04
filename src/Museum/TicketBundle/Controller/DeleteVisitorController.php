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
    public function deleteVisitorAction(Request $request)
    {
        $visitorService = $this->container->get('museum.visitorManagement');

        $lastTicket = $visitorService->deleteVisitor();

        /*
          si c'est le dernier visiteur de la liste,
          revenir automatiquement à la définition d'un nouveau visiteur
        */
        if ($lastTicket) {
            return $this->redirect( $this->generateUrl('visitor'));
        }

        $customer = $visitorService->visitorGetAssociatedCustomer();
        $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $customer);

        $formBuilder
            ->add('email', EmailType::class)
        ;
        $form = $formBuilder->getForm();


        $ticketFolder = $visitorService->visitorGetAssociatedTicketFolder();
        $locale = $request->getLocale();

        return $this->redirect( $this->generateUrl('recapTickets'));
        /*
        return $this->render('MuseumTicketBundle:Museum:recapAndPay.html.twig',[
            'recapAndPayForm' => $form->createView(),
            'tickets'=>$ticketFolder->getTickets(),
            'total' => $ticketFolder->getTotalAmount(),
            'locale' => $locale
        ]);*/
    }
}
