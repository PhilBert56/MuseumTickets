<?php

namespace Museum\TicketBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Museum\TicketBundle\Entity\Ticket;

class TicketTest extends WebTestCase
{
	// test customer hydratation
	public function testHydratation()
	{


    $ticket = new Ticket();
    $dateOfVisit = '20/09/2017';
    $ticket->setDateOfVisit($dateOfVisit);
    $ticket->setPriceCode(1);
    $ticket->setPrice(16);
    $ticket->setTicketCode('code 123456789');
    $ticket->setHalfDay(false);


    $this->assertEquals($dateOfVisit, $ticket->getDateOfVisit());
    $this->assertEquals(1, $ticket->getPriceCode());
    $this->assertEquals(16, $ticket->getPrice());
    $this->assertEquals('code 123456789', $ticket->getTicketCode());
    $this->assertEquals(false, $ticket->getHalfDay());

  }


}
