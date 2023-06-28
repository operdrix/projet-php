<?php

namespace App\Controller;

class AddrestauController extends AbstractController
{
  public function addrestau()
  {
    return $this->twig->render('addrestau.html.twig');
  }
}
