<?php

// api controller to return json results

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\Core\Database\DB;

class ApiController extends Controller
{
  
  // function to find a particular customer
  function searchCustomers(Request $req, Response $res)
  {
    $query = $req->getAttribute('query');

    // find and return customer in db
    $result = DB::findCustomer($query);
    $data = array('result' => $result);
    return $res->withJson($data);
  }
  
  // function to find a particular movie
  function searchMovie(Request $req, Response $res)
  {
    $query = $req->getAttribute('query');
    $result = DB::searchMovies($query);
    
    // base64 all images
    foreach ($result as $key => $field) {
      if ($field['img']) {
        $result[$key]['img'] = base64_encode($result[$key]['img']);
      }
    }

    // return json result
    $data = array('result' => $result);
    return $res->withJson($data);
  }
  
  // function to find all available platforms
  function getPlatforms(Request $req, Response $res)
  {
    $result = DB::selectAll('platform');

    $data = array('result' => $result);
    return $res->withJson($data);
  }
}