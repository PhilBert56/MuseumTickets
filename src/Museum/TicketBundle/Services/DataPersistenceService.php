<?php

namespace Museum\TicketBundle\Services;
use Museum\TicketBundle\Entity\WorkingDay;


class DataPersistenceService
{

  private $session;
  private $em;

  public function __construct(\Doctrine\ORM\EntityManager $entityManager,  $session)
  {
    $this->em = $entityManager;
    $this->session = $session;
  }

  public function storeData()
  {

      $ticketFolder = $this->session->get('ticketFolder');
      $tickets = $ticketFolder->getTickets() ;
      $customer = $ticketFolder->getCustomer();

      $customer->setTotalAmount($ticketFolder->getTotalAmount());

      $this->storeTicketsAndAssociatedVisitors($tickets,$customer);

      $this->em->persist($customer);
/*
      foreach ($tickets as $ticket) {

          $visitor = $ticket->getVisitor();
          $code = '987654321';
          $ticket->setTicketCode($code);
          $ticket->setCustomer($customer);
          $this->em->persist($visitor);
          $this->em->persist($ticket);

      }*/

      $this->em->flush();

      //return $this->render('MuseumTicketBundle:Museum:finalView.html.twig');

  }

    public function storeTicketsAndAssociatedVisitors($tickets, $customer){

      foreach ($tickets as $ticket) {

          $visitor = $ticket->getVisitor();

          /* génération du code sur le ticket - solution temporaire */
          $code = $this->encodeTicket($ticket);
          $ticket->setTicketCode($code);

          $ticket->setCustomer($customer);



          $this->em->persist($visitor);
          $this->em->persist($ticket);
          $this->refreshNumberOfVisitorPerDay($ticket->getDateOfVisit());

      }
    }

    public function encodeTicket($ticket){
        return '999999999';
    }

    public function refreshNumberOfVisitorPerDay($date){

        $workingDay = $this->em->getRepository('MuseumTicketBundle:WorkingDay')
            ->findOneByDate( $date );

        if(!$workingDay){
            $workingDay = new WorkingDay();
            $workingDay->setDate($date);
            $numberOfVisitors = 0;
        } else {
            $numberOfVisitors = $workingDay->getNumberOfVisitors();
        }
        $numberOfVisitors++;
        $workingDay->setNumberOfVisitors($numberOfVisitors);

        $this->em->persist($workingDay);
        //$this->em->flush();
    }




}
