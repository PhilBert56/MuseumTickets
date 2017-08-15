<?php

namespace Museum\TicketBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('MuseumTicketBundle:Default:index.html.twig');
    }
}
