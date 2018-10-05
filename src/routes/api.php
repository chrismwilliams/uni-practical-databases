<?php

// api routes which call the corresponding api controller method

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// search for customers
$app->get('/api/search_customers/{query}', 'ApiController:searchCustomers')->setName('apisearchcustomers');

// search for movies
$app->get('/api/search_movie/{query}', 'ApiController:searchMovie')->setName('apisearch_movie');

// return all platforms
$app->get('/api/platforms', 'ApiController:getPlatforms')->setName('apiplatforms');