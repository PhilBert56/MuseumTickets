<?php

namespace tests\AppBundle\Service;
use Museum\TicketBundle\Services\PriceFromBirthDateService;
use Museum\TicketBundle\Entity\Visitor;
use PHPUnit\Framework\TestCase;

/*
Calcul du tarif
Gratuit pour les moins de 4 ans - code prix = 0
Gratuit pour les demandeurs d'emploi (sur présentation d'un justificatif)
De 4 à 12 ans : 8 € - code prix = 1
Plus de 12 ans jusqu'à 60 ans : 16 € - code prix = 2
Plus de 60 ans : 12 € - code prix = 3
Tarif Réduit : 10 €
*/

class PricingTest extends TestCase {


  public function testPriceFunctionOfBirthDate(){

    $service = new PriceFromBirthDateService();
    $visitor = new Visitor();
    /* Gratuit pour les moins de 4 ans - code prix = 0 */
    $visitor->setBirthDate (new \DateTime('2013-09-30'));
    $dateOfVisit = new \DateTime('2017-09-30');
    $priceCode = $service->getPriceCode($visitor, $dateOfVisit);
    $this->assertSame( 0, $priceCode);

    /* De 4 à 12 ans - code prix = 1  */
    $visitor->setBirthDate (new \DateTime('2010-09-30'));
    $dateOfVisit = new \DateTime('2017-09-30');
    $priceCode = $service->getPriceCode($visitor, $dateOfVisit);
    $this->assertSame( 1, $priceCode);

    /* de 12 ans jusqu'à 60 ans - code prix = 2  */
    $visitor->setBirthDate (new \DateTime('2000-09-30'));
    $dateOfVisit = new \DateTime('2017-09-30');
    $priceCode = $service->getPriceCode($visitor, $dateOfVisit);
    $this->assertSame( 2, $priceCode);


    /* plus 60 ans - code prix = 3  */
    $visitor->setBirthDate (new \DateTime('1956-09-30'));
    $dateOfVisit = new \DateTime('2017-09-30');
    $priceCode = $service->getPriceCode($visitor, $dateOfVisit);
    $this->assertSame( 3, $priceCode);


  }

}
