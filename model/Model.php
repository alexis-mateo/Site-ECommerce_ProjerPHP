<?php
require_once File::build_path(array('config', 'Conf.php'));
class Model {
   
  public static $pdo;

  public static function Init()
  {
    $login = Conf::getGen("login");
    $password = Conf::getGen("password");
    $database = Conf::getGen("database");
    $hostname = Conf::getGen("hostname");   


      try {
        self::$pdo = new PDO("mysql:host=$hostname;dbname=$database", $login, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
        if (Conf::getDebug()) {
          echo $e->getMessage();
        } else {
          echo 'Une erreur est survenue <a href=""> retour a la page d\'accueil </a>';
        }
        die();
      }
  }

  public static function selectAll()
  {
    $table_name = static::$object;
    $class_name = 'Model' . ucfirst($table_name);
    try {
        $rep = Model::$pdo -> query("SELECT * FROM $table_name");
        $rep->setFetchMode(PDO::FETCH_CLASS, "$class_name");
        $tab_obj = $rep->fetchAll();
        return $tab_obj;
      } catch (PDOException $e) {
        if (Conf::getDebug()) {
          echo $e->getMessage(); // affiche un message d'erreur
        } else {
          echo 'Une erreur est survenue <a href=""> retour a la page d\'accueil </a>';
        }
        die();
      }
  }

  public static function saveGen($data)
  {
    $table_name = static::$object;
    $class_name = 'Model' . ucfirst($table_name);
    $primary_key = static::$primary;
    $primary_key_value = $_POST["$primary_key"];
    $stringInto = $table_name . '(' . $primary_key . ',';
    $stringValue = '';
    try {
        foreach ($data as $key => $value) 
          {
            $stringInto =  $stringInto . $key . ',';
            $stringValue = $stringValue . "'" . $value . "'" . ',';
          }
        $stringInto = rtrim($stringInto,",") . ')';
        $stringValue = rtrim($stringValue, ",");
        $sql = "INSERT INTO $stringInto VALUES (:primary_key, $stringValue)";
        $req_prep = Model::$pdo->prepare($sql);
        $values = array(
          "primary_key"=>$primary_key_value,
        );
        $req_prep->execute($values);
      } catch (PDOException $e) {
        if (Conf::getDebug()) {
          echo $e->getMessage();
        } else if ($e->getCode() == 23000){
          echo "Ce login utilisateur existe déjà !";
          return false;
        }
        else {
          echo 'Une erreur est survenue <a href=""> retour a la page d\'accueil </a>';
        }
        die();
    }
  }

  public static function select($primary_value)
  {
    $table_name = static::$object;
    $class_name = 'Model' . ucfirst($table_name);
    $primary_key = static::$primary;

    try {
        $sql="SELECT * from $table_name WHERE $primary_key=:nom_tag";
        // Préparation de la requête
        $req_prep = Model::$pdo->prepare($sql);
        $values = array("nom_tag"=>$primary_value,
              //nomdutag => valeur, ...
        );
        // On donne les valeurs et on exécute la requête
        $req_prep->execute($values);
        // On récupère les résultats comme précédemment
        $req_prep->setFetchMode(PDO::FETCH_CLASS, "$class_name");
        $tab_voit=$req_prep->fetchAll();
        // Attention, si il n'y a pas de résultats, on renvoie false
        if(empty($tab_voit))return false;
        return$tab_voit[0];
      } catch (PDOException $e) {
        if (Conf::getDebug()) {
          echo $e->getMessage(); // affiche un message d'erreur
        } else {
          echo 'Une erreur est survenue <a href="index.php"> retour a la page d\'accueil </a>';
        }
        die();
      }
  }

  public static function update($data) {
    $table_name = static::$object;
    $primary_key = static::$primary;
    $primary_key_value = $_POST["$primary_key"];
    $result = '';

    try {
      foreach ($data as $key => $value) {
        $result = $result .  $key . "='" . $value . "',";
      }
      $result = rtrim($result, ',');

      $sql = "UPDATE $table_name SET $result WHERE $primary_key = :primary_key;";
      $req = Model::$pdo->prepare($sql);
      
      $values = array('primary_key' => $primary_key_value);
      $req -> execute($values);
      
    } catch (PDOException $e) {
      if (Conf::getDebug()) {
        echo $e->getMessage(); // affiche un message d'erreur
      } else {
        echo 'Une erreur est survenue <a href="index.php"> retour a la page d\'accueil </a>';
      }
      die();
    }

  }

  public static function delete() {
    $table_name = static::$object;
    $primary_key = static::$primary;
    $primary_value = $_GET["$primary_key"];

    try {
      $sql = "DELETE FROM $table_name WHERE $primary_key = :primary_key;";
      $req = Model::$pdo->prepare($sql);
      
      $value = array('primary_key' => $primary_value);
      $req->execute($value);

    } catch (PDOException $e) {
      if (Conf::getDebug()) {
        echo $e->getMessage(); // affiche un message d'erreur
      } else {
        echo 'Une erreur est survenue <a href="index.php"> retour a la page d\'accueil </a>';
      }
      die();
    }
  }

}

Model::Init();
?>

