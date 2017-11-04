<?php

namespace tests\Museum\TicketBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');


        $this->assertEquals(500, $client->getResponse()->getStatusCode());


        $this->assertContains('Mus', $client->getResponse()->getContent());
    }
}
