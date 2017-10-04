<?php
namespace Museum\TicketBundle\Form;
namespace Museum\TicketBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Date;

use Museum\TicketBundle\Entity\Ticket;
use Museum\TicketBundle\TicketFolder\TicketFolder;
use Museum\TicketBundle\Entity\Visitor;
use Museum\TicketBundle\Form\VisitorFormType;
//use Museum\TicketBundle\TemporaryObjects\MuseumDay;


class VisitorController extends Controller
{
    /**
     * @Route("/Visitor" , name = "visitor")
     */
    public function visitorAction(Request $request)
    {
        $session = $this->get('session');

        $ticketFolder = $session->get('ticketFolder');

        /* création du visiteur et du ticket associé */
        $visitor = new Visitor();
        $ticket = new Ticket();
        $visitor->setTicket($ticket);
        $ticket->setVisitor($visitor);
        $ticket->setDateOfVisit(new \Datetime());

        $form = $this->get('form.factory')->create(VisitorFormType::class, $visitor);

        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            // On vérifie que les valeurs entrées sont correctes

            if ($form->isValid()) {


                $dateOfVisit = $form['ticket']['dateOfVisit']->getData();

                $dateService = $this->container->get('museum.isDateOfVisitOK');

                /* Vérifier si la date de visite est acceptable */
                $messages = $dateService->checkDateOfVisit($dateOfVisit);
                if ($messages[0]){
                  $this->addFlash('error', $messages[1]);
                  return $this->render('MuseumTicketBundle:Museum:visitorView.html.twig', [
                      'visitorForm' => $form->createView()
                  ]);
                }

                /*
                vérifier si heure de la commande n'impose pas seulement une demi-journée
                ou si la fermeture du musée est imminente
                */
                $messages = $dateService->checkHourOfVisit($dateOfVisit);
                if ($messages[0]){
                  $this->addFlash('error', $messages[1]);
                  return $this->render('MuseumTicketBundle:Museum:visitorView.html.twig', [
                      'visitorForm' => $form->createView()
                  ]);

                }

                /*
                  Déterminer le prix du billet et stocker les données saisies et validées
                */
                $priceFromBirthDateService = $this->container->get('museum.priceFromBirthDate');
                $visitor->setTicketInfo($dateOfVisit, $priceFromBirthDateService);

                $ticketFolder->addTicketToTicketFolder($ticket);

            }

            $this->addFlash('success', 'Prix du billet : '.$ticket->getPrice().' € ');
            return $this->render('MuseumTicketBundle:Museum:visitorView.html.twig', [
                'visitorForm' => $form->createView()
            ]);


        }

        return $this->render('MuseumTicketBundle:Museum:visitorView.html.twig',[
            'visitorForm' => $form->createView()
        ]);


    }


}
