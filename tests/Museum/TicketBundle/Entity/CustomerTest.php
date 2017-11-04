<?php

namespace test\Museum\TicketBundle\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Museum\TicketBundle\Entity\Customer;

class CustomerTest extends WebTestCase
{
	// test customer hydratation
	public function testHydratation()
	{
    $customer = new Customer();

    $customer->setEmail('toto@sfr.fr');
    $customer->setTotalAmount(50);

    $this->assertEquals('toto@sfr.fr', $customer->getEmail());
    $this->assertEquals(50, $customer->getTotalAmount());

  }


}
