<?php
// PDO connection credentials and settings class

namespace App\Core\Database;

use PDO;
use Dotenv\Dotenv;

$dotenv = new Dotenv(DIR_PATH);
$dotenv->load();

class Credentials
{

  // function to return PDO settings
  public static function database()
  {
    return [
      'connection' => 'mysql:host=' . getenv('DB_HOST'),
      'name' => getenv('DB_NAME'),
      'username' => getenv('DB_USER'),
      'password' => getenv('DB_PASS'),
      'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
      ]
    ];
  }
}

