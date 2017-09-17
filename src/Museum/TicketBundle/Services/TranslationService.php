<?php

namespace Museum\TicketBundle\Services;
//use Museum\TicketBundle\Entity\WorkingDay;


class TranslationService
{
  public function getTranslatedMessage($messageCode, $langue) {

      $translationFr = [
          1 => 'Vous ne pouvez pas commander de billet à cette date :',
      ];

      if ($langue == 'fr'){
          return $translationFr[$messageCode];
      }
      return "No translation available in this language";

  }

}
