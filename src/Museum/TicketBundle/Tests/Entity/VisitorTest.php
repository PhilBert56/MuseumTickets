<?php

namespace Museum\TicketBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Museum\TicketBundle\Entity\Visitor;

class VisitorTest extends WebTestCase
{
	// test customer hydratation
	public function testHydratation()
	{


    $visitor = new Visitor();
		$visitor->setName('Herman');
    $visitor->setFirstName('Jules');
    $visitor->setBirthDate('01/01/2010');
    $visitor->setCountry('France');
    $visitor->setReducePrice(false);


    $this->assertEquals('Herman', $visitor->getName());
		$this->assertEquals('Jules', $visitor->getFirstName());
    $this->assertEquals( '01/01/2010', $visitor->getBirthDate());
    $this->assertEquals('France', $visitor->getCountry());
    $this->assertEquals(false, $visitor->getReducePrice());

  }


}
