<?php

namespace Museum\TicketBundle\Services;
//use Museum\TicketBundle\Entity\WorkingDay;


class DateOfVisitService
{
  private $em;
  //private $locale;

  public function __construct(\Doctrine\ORM\EntityManager $entityManager)
  {
    $this->em = $entityManager;
    //$this->locale = $locale;
  }

  public function isDateOk($date) {

    /* Codes de refus d'une transaction
        0 => 'ok',
        1 => "La date selectionnée est dépassée",
        21 => "L'entrée au musée est gratuite le dimanche",
        22 => "L'entrée au musée est gratuite les jours fériés" ,
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

    //$emg = $this->em->getDoctrine()->getManager();
    $workingDay = $this->em->getRepository('MuseumTicketBundle:WorkingDay')
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

/*
  public function getTranslatedMessage($messageCode, $langue) {


      $translationFr = [
          1 => 'Vous ne pouvez pas commander de billet à cette date :',
      ];

      if ($langue == 'fr'){
          return $translationFr[$messageCode];
      }
      return "No translation available in this language";

  }
*/
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
