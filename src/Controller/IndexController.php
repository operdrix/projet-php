<?php

namespace App\Controller;

use PDO;
use Twig\Environment;

class IndexController extends AbstractController
{

  public function home(Environment $twig, PDO $pdo): string
  {
    $this->twig = $twig;
    $this->pdo = $pdo;

    // Récupère les données de la base de données
    $req = "SELECT * FROM restaurant";
    $statement = $this->pdo->prepare($req);
    $statement->execute();
    $restaurants = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Contexte Twig
    $context['page'] = array(
      'titre' => 'Liste des restaurants',
    );
    $context['restaurants'] = $restaurants;

    // Rendu du template Twig
    return $this->twig->render('index.html.twig', $context);
  }
}
