<?php

namespace App\Controller;

class IndexController extends AbstractController
{
  public function home(): string
  {
    return $this->twig->render('index.html.twig');
  }
}
