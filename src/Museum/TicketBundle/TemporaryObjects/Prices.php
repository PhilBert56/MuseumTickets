<?php

namespace Museum\TicketBundle\TemporaryObjects;

use Museum\TicketBundle\Entity;
use Museum\TicketBundle\Entity\Customer;
use Symfony\Component\HttpFoundation\Session\Session;

class Prices
{
  public $priceChild;
  public $priceAdult;
  public $priceSenior;
  public $priceReduced;

  public function __construct()
  {

    $fileName = "..\src\Museum\TicketBundle\Data\museumPricing.csv";

    if (($handle = fopen($fileName, "r")) !== FALSE) {

      while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        if ($data[0] == 1) $this->priceChild = $data[1];
        if ($data[0] == 2) $this->priceAdult = $data[1];
        if ($data[0] == 3) $this->priceSenior = $data[1];
        if ($data[0] == 4) $this->priceReduced = $data[1];
      }

    }

  }

}
