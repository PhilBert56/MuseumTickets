<?php

namespace tests\AppBundle\Service;
use Museum\TicketBundle\Services\DateOfVisitService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/*
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

class DateOfVisitTest extends WebTestCase {


  public function testDateOfVisit(){

    //$dateService = $this->getMock('Museum\TicketBundle\Services\DateOfVisitService');

    $kernel = static::createKernel();
    $kernel->boot();

    $container = $kernel->getContainer();

    $dateService = $container->get('museum.isDateOfVisitOK');

    $dateOfVisit = new \DateTime('2016-10-08'); // date dépassée
    $this->assertSame( 1, $dateService->isDateOk($dateOfVisit));


    $dateOfVisit = new \DateTime('2017-10-15'); // un dimanche
    $this->assertSame( 21, $dateService->isDateOk($dateOfVisit));


    $dateOfVisit = new \DateTime('2017-11-01'); // 11 novembre
    $this->assertSame( 33, $dateService->isDateOk($dateOfVisit));

    $dateOfVisit = new \DateTime('2017-12-25'); // Noël
    $this->assertSame( 34, $dateService->isDateOk($dateOfVisit));

    $dateOfVisit = new \DateTime('2018-05-01'); // Premier mai
    $this->assertSame( 32, $dateService->isDateOk($dateOfVisit));


  }


}
