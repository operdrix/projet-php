<?php

namespace App\Controller;

use Twig\Environment;

abstract class AbstractController
{
  public function __construct(
    protected Environment $twig
  ) {
  }
}
