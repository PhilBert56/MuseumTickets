<?php

namespace Museum\TicketBundle\Services;
//use Museum\TicketBundle\Entity\WorkingDay;


class PriceFromBirthDateService
{
  
  public function getPriceCode($visitor, $dateOfVisit){
      /* Gestion date anniversaire pour determiner age du visiteur */
      $birthDate = $visitor->getBirthDate();
      $age = $visitor->age($birthDate, $dateOfVisit);
      $priceCode = -1;
      if($age < 4 ) $priceCode = 0;
      if($age >= 4 && $age <= 12 ) $priceCode = 1;
      if($age > 12 && $age < 60 ) $priceCode = 2;
      if($age >= 60 ) $priceCode = 3;
      if($age > 12 && $visitor->getReducePrice() ) $priceCode = 4 ;
      return $priceCode;
  }


  public function getPrice($priceCode) {

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

}
