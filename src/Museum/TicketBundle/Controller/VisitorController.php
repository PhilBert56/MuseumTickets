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
dump($dateOfVisit);
                $dateService = $this->container->get('museum.isDateOfVisitOK');

                $codeDateOk = $dateService->isDateOk($dateOfVisit);

                /* Si date infaisable */
                if ($codeDateOk !== 0) {
                    $message1 = $this->getTranslatedMessage(1, 'fr');
                    $message2 = $this->getRefusalMotivation($codeDateOk);

                    $this->addFlash('error', $message1.' '.$message2);

                    return $this->render('MuseumTicketBundle:Museum:visitorView.html.twig', [
                        'visitorForm' => $form->createView()
                    ]);
                } else {
                    /* date acceptée */
                    $ticketFolder->setDateOfVisit($dateOfVisit);
                }

                $hourIsOkCode = $this->isFullDayOrderStillPossible($dateOfVisit);
                /* si heure du jour impose demi-journée ou fermeture imminente */
                if( $hourIsOkCode == 51 && !$ticket->getHalfDay()) {
                    /* l'utilisateur doit cocher demi-journée */
                    $message1 = $this->getTranslatedMessage(1, 'fr');
                    $message2 = $this->getRefusalMotivation($hourIsOkCode);

                    $this->addFlash('error', $message1.' '.$message2);

                    return $this->render('MuseumTicketBundle:Museum:visitorView.html.twig', [
                        'visitorForm' => $form->createView()
                    ]);
                }
                if( $hourIsOkCode == 52 ) {
                    $message1 = $this->getTranslatedMessage(1, 'fr');
                    $message2 = $this->getRefusalMotivation($hourIsOkCode);

                    $this->addFlash('error', $message1.' '.$message2);
                    return $this->render('MuseumTicketBundle:Museum:visitorView.html.twig', [
                        'visitorForm' => $form->createView()
                    ]);
                }

                $this->setTicketInfo($visitor, $dateOfVisit);
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


    function age($dateOfBirth, $dateOfVisit) {

    /* Retourne l'âge qu'aura la personne née le $dateOfBirth (objet date) à la date $date (objet date)*/
        $year_diff  = $dateOfVisit->format("Y") - $dateOfBirth->format("Y");
        $month_diff = $dateOfVisit->format("m") - $dateOfBirth->format("m");
        $day_diff   = $dateOfVisit->format("d") - $dateOfBirth->format("d");
        if ($month_diff < 0) $year_diff--;
        if ($month_diff==0 && $day_diff <= 0  ) $year_diff--;
        return $year_diff;
    }


    function createTicket($visitor, $dateOfVisit) {

        $ticket = new Ticket();
        $ticket->setDateOfVisite($dateOfVisit);
        $ticket->setVisitor($visitor);

        /* Calcul du code tarif */
        $priceCode = $this->getPriceCode($visitor,$dateOfVisit );
        $price = $this->getPrice($priceCode);
        $ticket->setPriceCode($priceCode);
        $ticket->setPrice($price);
        //$ticket->setHalfDay($visitor->getHalfDayVisitor());

        return $ticket;
    }


    function setTicketInfo($visitor, $dateOfVisit) {

        $ticket = $visitor->getTicket();
        $ticket->setDateOfVisit($dateOfVisit);

        /* Calcul du code tarif */
        $priceCode = $this->getPriceCode($visitor,$dateOfVisit );
        $price = $this->getPrice($priceCode);
        /* Vérifier si demi-journée, si vrai, prix divisé par 2 */
        if ($ticket->getHalfDay() ) $price = $price / 2;

        $ticket->setPriceCode($priceCode);
        $ticket->setPrice($price);

        return $ticket;
    }

    function getPrice($priceCode) {

        $price = -1;

        /* princing in CSV file museumPricing.csv */

        $row = 1;

        $fileName = "..\src\Museum\TicketBundle\Data\museumPricing.csv";

        if (($handle = fopen($fileName, "r")) !== FALSE) {

            $iPriceCode =0;
            $iPrice = 1;

            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $num = count($data);
                if ($row == 1){
                    if ($data[0] == 'priceCode') $iPriceCode = 0;
                    if ($data[1] == 'priceCode') $iPriceCode = 1;
                    if ($data[0] == 'price') $iPrice = 0;
                    if ($data[1] == 'price') $iPrice = 1;

                } else {
                    if ($data[$iPriceCode] == $priceCode) return $data[$iPrice];
                }
                $row++;
            };
            fclose($handle);
        };

        return $price;

    }



    function getPriceCode($visitor, $dateOfVisit){

        /* Gestion date anniversaire pour determiner age du visiteur */
        $birthDate = $visitor->getBirthDate();
        $age = $this->age($birthDate, $dateOfVisit);
        $priceCode = -1;
        if($age < 4 ) $priceCode = 0;
        if($age >= 4 && $age <= 12 ) $priceCode = 1;
        if($age > 12 && $age < 60 ) $priceCode = 2;
        if($age >= 60 ) $priceCode = 3;
        if($age > 12 && $visitor->getReducePrice() ) $priceCode = 4 ;

        return $priceCode;
    }



    function isFullDayOrderStillPossible($date)
    {

        /* Vérifier si l'heure permet de commander un billet à la journée */

        $timeZone = 'Europe/Paris';
        $timestamp = time();
        $today = new \DateTime("now", new \DateTimeZone($timeZone));
        $today->setTimestamp($timestamp); //adjust the object to correct timestamp

        //$today = new \DateTime();
        $hour = $today->format("H");
        $todayDate = $today->format('d/m/Y');
        $dateDate = $date->format('d/m/Y');

        if ($dateDate == $todayDate && $hour >= 14) {
            /* Heure de fermeture imminente codée en dur = SOLUTION TEMPORAIRE à améliorer !*/
            if ($hour >= 16){return 52; }
            else { return 51;}
        }

        return 0;
    }



    function insertTicketIntoTicketFolder($ticket, $request)
    {
        $session = $this->get('session');

        $ticketFolder = $session->get('ticketFolder');

        $tickets = $ticketFolder->getTickets();

        $visitor = $ticket->getVisitor();
        $name = $visitor->getName();
        $firstName = $visitor->getFirstName();

        $isInFolder = false;

        /* si un ticket a déjà été généré pour ce visiteur alors c'est une simple modification */
        foreach ($tickets as $t) {
            echo 'encore un ticket';
            if (    $t->getVisitor()->getName() == $name
                &&  $t->getVisitor()->getFirstName() == $firstName)
            {
                $t = $ticket;
                $isInFolder = true;
                break;
            }
        }

        /* si le ticket n'était pas encore inséré dans le ticket Folder, alors insertion du nouveau ticket */
        //echo 'in folder = ', $isInFolder;
        if (!$isInFolder ) {
            $tickets[] = $ticket;
        }
        $ticketFolder->setTickets($tickets);
        $session->set('ticketfolder', $ticketFolder);
    }



/* A remettre en serviCE */


    public function getTranslatedMessage($messageCode, $langue) {


        $translationFr = [
            1 => 'Vous ne pouvez pas commander de billet à cette date :',
        ];

        if ($langue == 'fr'){
            return $translationFr[$messageCode];
        }
        return "No translation available in this language";

    }

    public function getRefusalMotivation($codeRefus) {

  /* la traduction des messages en anglis est assurée par le traducteur des Symfony */
        $motifRefusFr = [
            0 => 'ok',
            1 => "La date selectionnée est dépassée",
            21 => "L'entrée au musée est gratuite le dimanche",
            22 => "L'entrée au musée est gratuite les jours fériés" ,
            31 => "Le musée est fermé le mardi",
            32 => "Le musée est fermé 1er mai",
            33 => "Le musée est fermé le 1er novembre",
            34 => "Le musée est fermé le 25 décembre",
            4 => "Capacité maximum du musée atteinte ce jour (Plus de 1000 billets déjà vendus)",
            51 => "Il n'est plus posssible de commander de billets que pour cet après-midi",
            52 => "Trop tard pour commander un billet aujourd'hui"
        ];


        return $motifRefusFr[$codeRefus];

    }


}
