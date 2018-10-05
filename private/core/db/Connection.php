<?php
// Class the makes a database PDO connection using the config from Credentials.php 

namespace App\Core\Database;

use PDO;
use PDOException;
use App\Core\Database\Credentials;

class Connection
{

  public static function make()
  {
    // require log in details
    $config = Credentials::database();

    // try to log-in
    try {
      return new PDO(
        $config['connection'] . ';dbname=' . $config['name'],
        $config['username'],
        $config['password'],
        $config['options']
      );
    } catch (PDOException $e) {
      // can't connect, kill the application
      die($e->getMessage());
    }
  }
}