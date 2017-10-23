<?php

namespace Museum\TicketBundle\TemporaryObjects;

use Museum\TicketBundle\Entity;
use Museum\TicketBundle\Entity\Customer;
use Symfony\Component\HttpFoundation\Session\Session;

class AgeLimits
{
  public $baby;
  public $teen;
  public $senior;

  public function __construct()
  {

    $fileName = "..\src\Museum\TicketBundle\Data\museumAges.csv";

    if (($handle = fopen($fileName, "r")) !== FALSE) {

      while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        if ($data[0] == 'baby') $this->baby = $data[1];
        if ($data[0] == 'teen') $this->teen = $data[1];
        if ($data[0] == 'senior') $this->senior = $data[1];
      }

    }

  }

}
