<?php

namespace tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $this->client = static::createClient(
          array(),
          array(
            'HTTP_HOST' => 'http://127.0.0.1:8000', //dependent on server
        ));
        $this->client->followRedirects(true);

        $crawler = $client->request('GET', '/');

        $this->assertEquals(500, $client->getResponse()->getStatusCode());
        //$this->assertContains('Mus', $crawler->filter('#container h1')->text());
    }
}
