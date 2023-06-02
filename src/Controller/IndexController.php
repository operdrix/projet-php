<?php

namespace App\Controller;

class IndexController extends AbstractController
{
  public function home(): string
  {
    $mavariable = array(
      'name' => 'Mon',
      'value' => 'Valeur',
      'type' => 'text',
    );
    $context['var'] = $mavariable;
    return $this->twig->render('index.html.twig', $context);
  }
}
