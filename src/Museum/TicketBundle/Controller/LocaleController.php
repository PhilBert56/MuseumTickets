<?php

namespace Museum\TicketBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Museum\TicketBundle\TemporaryObjects\AgeLimits;
use Museum\TicketBundle\TemporaryObjects\Prices;

class LocaleController extends Controller
{
/**
 * @Route("/change-language/", name="changelocale")
*/
  public function changelocaleAction(Request $request)
  {
      $locale = $request->getLocale();
      if ($locale == 'en'){
        $request->setLocale('fr');
        $this->get('session')->set('_locale', 'fr');
      }
      else{
        $request->setLocale('en');
        $this->get('session')->set('_locale', 'en');
      }

    $ages = new AgeLimits();
    $prices = new Prices();

    return $this->redirectToRoute("accueil", [
      'ages' => $ages,
      'prices' => $prices
    ]);
  }
}
