<?php

// middleware to handle any errors set and to add them to the current view

namespace App\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ErrorsMiddleware extends Middleware {

  public function __invoke(Request $req, Response $res, $next) {
    // check if the session['errors'] set
    if(isset($_SESSION['errors'])) {
      // add the the view if true
      $this->container->view->getEnvironment()->addGlobal('errors', $_SESSION['errors']);
      // unset to remove from next request
      unset($_SESSION['errors']);
    }
    
    // call next
    $res = $next($req, $res);
    return $res;
  }
}