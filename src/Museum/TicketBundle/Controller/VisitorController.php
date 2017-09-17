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
use Museum\TicketBundle\TemporaryObjects\MuseumDay;


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
                /* Vérifier si l'heure permet de commander un billet à la journée */

                $dateOfVisit = $form['ticket']['dateOfVisit']->getData();

                $dateService = $this->container->get('museum.isDateOfVisitOK');

                $codeDateOk = $dateService->isDateOk($dateOfVisit);


                $translation = $this->container->get('museum.translation');
                /* Si date infaisable */
                if ($codeDateOk !== 0) {
                    $message1 = $translation->getTranslatedMessage(1, 'fr');
                    $message2 = $dateService->getRefusalMotivation($codeDateOk);

                    $this->addFlash('error', $message1.' '.$message2);

                    return $this->render('MuseumTicketBundle:Museum:visitorView.html.twig', [
                        'visitorForm' => $form->createView()
                    ]);
                } else {
                    /* date acceptée */
                    $ticketFolder->setDateOfVisit($dateOfVisit);
                }

                $hourIsOkCode = $dateService->isFullDayOrderStillPossible($dateOfVisit);

                /* si heure du jour impose demi-journée ou fermeture imminente */
                if( $hourIsOkCode == 51 && !$ticket->getHalfDay()) {
                    /* l'utilisateur doit cocher demi-journée */
                    $message1 = $translation->getTranslatedMessage(1, 'fr');
                    $message2 = $dateService->getRefusalMotivation($hourIsOkCode);

                    $this->addFlash('error', $message1.' '.$message2);

                    return $this->render('MuseumTicketBundle:Museum:visitorView.html.twig', [
                        'visitorForm' => $form->createView()
                    ]);
                }
                if( $hourIsOkCode == 52 ) {
                    $message1 = $translation->getTranslatedMessage(1, 'fr');
                    $message2 = $dateService->getRefusalMotivation($hourIsOkCode);

                    $this->addFlash('error', $message1.' '.$message2);
                    return $this->render('MuseumTicketBundle:Museum:visitorView.html.twig', [
                        'visitorForm' => $form->createView()
                    ]);
                }

                $priceFromBirthDateServive = $this->container->get('museum.priceFromBirthDate');
                $visitor->setTicketInfo($dateOfVisit, $priceFromBirthDateServive);
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
