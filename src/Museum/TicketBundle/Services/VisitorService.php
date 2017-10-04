<?php

namespace Museum\TicketBundle\Services;

use Symfony\Component\HttpFoundation\Request;
use Museum\TicketBundle\Entity\Customer;
use Symfony\Component\HttpFoundation\Session\Session;

class VisitorService {

  private $session;

  public function __construct(Session $session)
  {
    $this->session = $session;
  }


  public function deleteVisitor() {

    $ticketFolder = $this->session->get('ticketFolder');

    $request = Request::createFromGlobals();
    $firstName = $request->query->get('firstName');
    $lastName = $request->query->get('lastName');

    $ticketFolder->cancelVisitorAndAssociatedTicket($firstName, $lastName);
    $totalAmount = $ticketFolder->getTotalAmount();
    $tickets = $ticketFolder->getTickets();

    $lastTicket = false;
    if ( count ($tickets) == 0) $lastTicket = true;
    return $lastTicket;

  }


  public function visitorGetAssociatedCustomer() {

    $ticketFolder = $this->session->get('ticketFolder');
    $customer = $ticketFolder->getCustomer();
    if (!$customer) $customer = new Customer();
    return $customer;
  }



  public function visitorGetAssociatedTicketFolder() {

    return $this->session->get('ticketFolder');

  }




}
