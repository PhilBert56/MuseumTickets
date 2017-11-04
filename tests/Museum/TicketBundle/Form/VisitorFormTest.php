<?php

namespace Museum\TicketBundle\Tests\Form;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class VisitorControllerTest extends WebTestCase
{
    // …

    public function testAddVisitor()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/Visitor');

        $form = $crawler->selectButton('Confirmer')->form();



        /* Modèle du cours
        $form['food[username]'] = 'John Doe';
        $form['food[entitled]'] = 'Plat de pâtes';
        $form['food[calories]'] = 600;
        $crawler = $client->submit($form);
*/
        $form['ticketbundle_visitor[country]'] = 'GB';

        $this->assertContains('France', $client->getResponse()->getContent());
        //echo $client->getResponse()->getContent();
    }
}
