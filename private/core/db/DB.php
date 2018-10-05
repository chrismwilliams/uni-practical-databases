<?php
// Main Database class that passes calls to the PDO query instance Query.php

namespace App\Core\Database;

class DB
{
  // Static private PDO query instance
  private static $queryDB;

  // Store PDO query instance
  public static function storeQuery($query)
  {
    static::$queryDB = $query;
  }

  // Every call passed to the $queryDB instance with the params if set, defaults to empty array.
  public static function __callStatic($method, $params = [])
  {
    return static::$queryDB->$method(...$params);
  }
}