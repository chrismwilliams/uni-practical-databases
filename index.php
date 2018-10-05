<?php 

// all application traffic directed to this file

// require the bootstrap file to load and set dependencies
require 'bootstrap.php';

// Start the app by calling the slim router run method
$app->run();