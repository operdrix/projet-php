<?php

namespace App\Controller;
use PDO;
use Twig\Environment;

class AddrestauController extends AbstractController
{

  //Traitement
  public function addrestau(Environment $twig, PDO $pdo):string
  {

    $this->twig = $twig;
    $this->pdo = $pdo;
    //Context Twig
    $context['page'] = array(
      'titre' => 'Ajouter un restaurant',
    );

    /**
     * Si le formulaire d'ajout de restaurant est rempli, alors on envoie les données en base.
     */
    if (!empty($_POST)) {

      //Récupération des données
      $nom = $_POST['nom'] ?? '';
      $description = $_POST['description'] ?? '';
      $nom_proprietaire = $_POST['nom_proprietaire'] ?? '';
      $adresse = $_POST['adresse'] ?? '';
      $email = $_POST['email'] ?? '';
      $tel = $_POST['tel'] ?? '';
      $photo_url = $_POST['photo_url'] ?? '';

      //Est-ce que le restaurant existe déjà ?
      $req = "SELECT COUNT(*) FROM restaurant WHERE nom = ?";
      $statement = $this->pdo->prepare($req);
      $statement->execute([$nom]);
      $count = $statement->fetchColumn(); //On récupère le nombre de restaurant ayant le nom.
      
      //Si il n'y a pas de restaurants qui portent le même nom...
      if ($count <= 0) {
        //Préparation de la requête SQL pour insérer
        $req = "INSERT INTO `restaurant` (`id`, `nom`, `description`, `adresse`, `telephone`, `url_photo`, `email`, `nom_proprietaire`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?);";
        $statement = $pdo->prepare($req);
        //Envoi de la requête
        $statement->execute(array(NULL, $nom, $description, $adresse, $tel, $photo_url, $email, $nom_proprietaire));
 
      } else {
        echo "Le restaurant existe déjà";
      }

    }

    return $twig->render('addrestau.html.twig', $context);

  }
}
