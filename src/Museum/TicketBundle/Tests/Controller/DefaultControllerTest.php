<?php

namespace Museum\TicketBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');


        $this->assertEquals(500, $client->getResponse()->getStatusCode());


        $this->assertContains('Musée', $client->getResponse()->getContent());
    }
}
