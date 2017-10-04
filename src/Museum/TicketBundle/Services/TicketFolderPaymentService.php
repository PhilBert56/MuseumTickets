<?php

namespace Museum\TicketBundle\Services;

class TicketFolderPaymentService
{

    public function processPayment($request, $ticketFolder) {

      if ( $ticketFolder->getPaymentAlreadyProcessed() ) return false;

      $totalAmount = $ticketFolder->getTotalAmount();

      $token = $request->request->get('stripeToken');

      \Stripe\Stripe::setApiKey("sk_test_6t3qfq3AknEGeNqYq8nzGEDs");

      \Stripe\Charge::create(array(
          "amount" => $totalAmount * 100,
          "currency" => "EUR",
          "source" => $token,
          "description" => "Museum tickets"
      ));

      return true;

    }



}
