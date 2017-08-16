<?php
namespace Museum\TicketBundle\Form;
namespace Museum\TicketBundle\Controller;
//namespace TicketBundle\Controller;
//namespace TicketBundle\TicketFolder;

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
                $codeDateOk = $this->isDateOk($dateOfVisit);
                /* Si date infaisable */
                if ($codeDateOk !== 0) {
                    $message1 = $this->getTranslatedMessage(1, 'fr');
                    $message2 = $this->getRefusalMotivation($codeDateOk, 'fr');
                    return $this->render('MuseumTicketBundle:Museum:visitorView.html.twig', [
                        'visitorForm' => $form->createView(),
                        'message1' => $message1 ,
                        'message2'=> $message2
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
                    $message2 = $this->getRefusalMotivation($hourIsOkCode, 'fr');
                    return $this->render('MuseumTicketBundle:Museum:visitorView.html.twig', [
                        'visitorForm' => $form->createView(),
                        'message1' => $message1 ,
                        'message2'=> $message2
                    ]);
                }
                if( $hourIsOkCode == 52 ) {
                    $message1 = $this->getTranslatedMessage(1, 'fr');
                    $message2 = $this->getRefusalMotivation($hourIsOkCode, 'fr');
                    return $this->render('MuseumTicketBundle:Museum:visitorView.html.twig', [
                        'visitorForm' => $form->createView(),
                        'message1' => $message1 ,
                        'message2'=> $message2
                    ]);
                }

                $this->setTicketInfo($visitor, $dateOfVisit);
                //$this->insertTicketIntoTicketFolder($ticket,$request);
                $ticketFolder->addTicketToTicketFolder($ticket);

            }

            return $this->render('MuseumTicketBundle:Museum:visitorView.html.twig', [
                'visitorForm' => $form->createView(),
                'message1' => ' PRICE = ',
                'message2' => $ticket->getPrice()
            ]);


        }

        return $this->render('MuseumTicketBundle:Museum:visitorView.html.twig',[
            'visitorForm' => $form->createView(),
            'message1' => '',
            'message2' => '',
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

        /* otion 1 = pricing in BD */

        /* ______________________________________________________
        $em = $this->getDoctrine()->getManager();
        $dbPricing = $em->getRepository('TicketBundle:Pricing')
            ->findOneByPriceCode( $priceCode );

        if (!$dbPricing) {
            throw $this->createNotFoundException(
                'No product found for price code '.$priceCode
            );
        } else $price = $dbPricing->getPrice();
        ________________________________________________________*/

        /* option 2 = princing in CSV file */

        $row = 1;

        $fileName = "..\src\TicketBundle\Data\museumPricing.csv";

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

        $today = new \DateTime();
        $hour = $today->format("H");
        $todayDate = $today->format('d/m/Y');
        $dateDate = $date->format('d/m/Y');
 ;

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


    public function isDateOk($date) {

        /* Codes de refus d'une transaction
            0 => 'ok',
            1 => "La date selectionnée est dépassée",
            21 => "L'entrée au musée est gratuit le dimanche",
            22 => "L'entrée au musée est gratuit les jours fériés" ,
            31 => "Le musée est fermé le mardi",
            32 => "Le musée est fermé le 1er mai",
            33 => "Le musée est fermé le 1er novembre",
            34 => "Le musée est fermé le 25 décembre",
             4 => "Capacité maximum du musée atteinte ce jour (Plus de 1000 billets déjà vendus)",
            51 => "Il n'est plus posssible de commander de billets que pour cet après-midi",
            52 => "Trop tard pour commander un billet aujourd'hui"
        */

        // La date est-elle dépassée ?
        $codePast = $this->isDateAlreadyPast($date);
        if (!($codePast == 0) )return $codePast;
        // Jour férié chomé (1er mai, 1er nov, noel) ou le musée est fermé
        $codeClosed = $this->isMuseumClosedForPublicHolyday($date);
        if (!($codeClosed == 0)) return $codeClosed;

        // Jour férié où le musée est gratuit
        $codeHoliday = $this->isDateAPublicHoliday($date);
        if (!($codeHoliday == 0)) return $codeHoliday;

        // Jour de fermeture hebdomadaire le mardi ou gratuit le dimanche
        $codeDayOfWeekClosedOrFree = $this->isSpecialDayInWeek($date);
        if (!($codeDayOfWeekClosedOrFree == 0))  return $codeDayOfWeekClosedOrFree;

        // Plus de 1000 visiteurs ?
        $capacityMaxCase = $this->isCapacityMaxReached($date);
        if (!($capacityMaxCase == 0)) return $capacityMaxCase;

        // Si on arrive jusque là, jour de l'année acceptable pour une réservation
        return 0;

    }


    public function isDateAlreadyPast($date){

        $time = $this->translateDateTimeIntoValue($date);
        $time0 = $this->translateDateIntoValue(new \DateTime());

        // La date est-elle dépassée ?
        if ( $time < $time0 )return 1;

        return 0;
    }


    public function isMuseumClosedForPublicHolyday($date){

        $year = $date->format("Y");
        $time = $this->translateDateTimeIntoValue($date);

        $premierMai =  mktime(0, 0, 0, 5, 1, $year);
        $premierNovembre =  mktime(0, 0, 0, 11, 1, $year);
        $noel = mktime(0, 0, 0, 12, 25, $year);

        // Jour férié chomé ?

        if ($time == $premierMai) return 32;
        if ($time == $premierNovembre) return 33;
        if ($time == $noel) return 34;

        return 0;
    }


    public function isDateAPublicHoliday($date) {

        $year = $date->format("Y");
        $time = $this->translateDateTimeIntoValue($date);
        $holidays = $this->getHolidayTable($year);
        // Jours fériés où le muséee est gratuit
        if(in_array($time, $holidays))return 22;
        return 0;
    }


    public function isSpecialDayInWeek($date)
    {
        // Jours de la semaine à exclure : dimanche et mardi
        $jj = $date->format('w');

        // Dimanche ?
        if ($jj == 0) return 21;

        // Mardi ?
        if ($jj == 2) return 31;

        return 0;
    }


    public function isCapacityMaxReached($date){

        // Plus de 1000 visiteurs ?

        $em = $this->getDoctrine()->getManager();
        $workingDay = $em->getRepository('MuseumTicketBundle:WorkingDay')
            ->findOneByDate( $date );

        if(!$workingDay){
            //throw $this->createNotFoundException('No day found');
        } else {

            if ( $workingDay->getNumberOfVisitors() > 1000) {
                return 4;
            }
        }

        return 0;
    }




    public function getHolidayTable($year){

        $easterDate = easter_date($year);
        $easterDay = date('j', $easterDate);
        $easterMonth = date('n', $easterDate);
        $easterYear = date('Y', $easterDate);

        $holidays =
            [
                mktime(0, 0, 0, 5, 8, $year),// Victoire des allies
                mktime(0, 0, 0, 7, 14, $year),// Fete nationale
                mktime(0, 0, 0, 8, 15, $year),// Assomption
                mktime(0, 0, 0, 11, 1, $year),// Toussaint
                mktime(0, 0, 0, 11, 11, $year),// Armistice

                // Jour feries qui dependent de paques
                mktime(0, 0, 0, $easterMonth, $easterDay + 2, $easterYear),// Lundi de Paques
                mktime(0, 0, 0, $easterMonth, $easterDay + 40, $easterYear),// Ascension
                mktime(0, 0, 0, $easterMonth, $easterDay + 51, $easterYear), // Pentecote
            ];
        return $holidays;
    }



    public function getRefusalMotivation($codeRefus, $langue) {

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

        if ($langue == 'fr'){
            return $motifRefusFr[$codeRefus];
        }
        return "No translation available in this language";
    }


    public function getTranslatedMessage($messageCode, $langue) {
        /*
            Table des messages à traduire :
            1 - Date invalide
        */

        $translationFr = [
            1 => 'Vous ne pouvez pas commander de billet à cette date :',
        ];

        if ($langue == 'fr'){
            return $translationFr[$messageCode];
        }
        return "No translation available in this language";

    }

    public function translateDateTimeIntoValue($date)
    {
        $hour = $date->format("H");
        $minute = $date->format("i");
        $second = $date->format("s");
        $month = $date->format("n");
        $day = $date->format("j");
        $year = $date->format("Y");
        return mktime($hour, $minute, $second, $month, $day, $year);
    }



    public function translateDateIntoValue($date)
    {
        $month = $date->format("n");
        $day = $date->format("j");
        $year = $date->format("Y");
        return mktime(0, 0, 0, $month, $day, $year);
    }







}
