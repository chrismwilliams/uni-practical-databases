<?php
// file to load dependencies in the correct order

// Classes
use App\Core\Database\Connection;
use App\Core\Database\QueryDB;
use App\Core\Database\DB;

// Start Object buffering && sessions
ob_start();
session_start();

// Define project root
define("DIR_PATH", __DIR__);

// Import libraries, Classes & custom functions from Composer dependancy manager
require_once './vendor/autoload.php';

// require slim & settings
require_once './private/core/router/slimConfig.php';

// import get / post / api routes
require_once './src/routes/get.php';
require_once './src/routes/post.php';
require_once './src/routes/api.php';


// Create Database instance and the Query
DB::storeQuery(new QueryDB(Connection::make()));





