<?php

namespace Museum\TicketBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Museum\TicketBundle\TemporaryObjects\Tests;

class TestController extends Controller
{
/**
 * @Route("/test", name="test")
*/
  public function testAction(Request $request)
  {
    $localeRequest = $request->getLocale();
    echo '$localRequest = '.$localeRequest.'</br>';

    $session = $this->get('session');
    $localeSession = $session->get('locale');
    echo '  $localSession = '.$localeSession.'</br>';

    $request->setLocale($localeSession);
    $request->getSession()->set('_locale', $localeSession);

    $locale = $request->getLocale();
    echo 'la langue locale (de $request) est maintenant ',$locale;

    $tests = new Tests();

    $form = $this->createFormBuilder($tests)->getForm();

    if ($request->isMethod('POST')) {
    /* si on a cliqué sur le bouton Nouveau Visiteur,
    on est redirigé vers la vue de définition d'un visiteur */
      $form->handleRequest($request);

      echo ' </br> on change de langue </br>';
      $nouvelleLangue = $this->changeLangue($localeRequest);
      $this->changeLocale($request, $session, $nouvelleLangue);
      //$nouvelleLangue ='fr';
      echo ' </br> nouvelle Langue = '.$nouvelleLangue.'</br>';
      $localeRequest = $request->getLocale();
      $localeSession = $session->get('locale');
/*
      $translator = $this->get('translator');
      $texteTraduit = $translator->trans('Message in English');
      echo 'traduction = ',$texteTraduit,'</br>';
*/
      return $this->render('MuseumTicketBundle:Museum:test.html.twig', array(
        'testsForm' => $form->createView(),
        'langue_localeSession' => $localeSession,
        'langue_localeRequest' => $localeRequest,
        'message' => 'on a changé de langue, on passe en :'.$nouvelleLangue
      ) );

    }

    return $this->render('MuseumTicketBundle:Museum:test.html.twig', array(
      'testsForm' => $form->createView(),
      'langue_localeSession' => $localeSession,
      'langue_localeRequest' => $localeRequest,
      'message' => 'rien à dire'
    ) );


  }

  private function changeLangue($locale) {

    if ($locale == 'fr'){
      return 'en';
    } else {
      return 'fr';
    }
  }

  private function changeLocale($request,$session,$nouvelleLocale){
    $request->setLocale($nouvelleLocale);
    $session->set('locale', $nouvelleLocale );
    $request->getSession()->set('_locale', $nouvelleLocale);
  }
}
