<?php

// middleware to handle form input which places the input in the next request

namespace App\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class InputMiddleware extends Middleware {

  public function __invoke(Request $req, Response $res, $next) {
    
    // check if the session['oldInput'] is set
    if(isset($_SESSION['oldInput'])) {
      // if true add it to the next view
      $this->container->view->getEnvironment()->addGlobal('oldInput', $_SESSION['oldInput']);
    }
    
    // add the old input to the global session
    $_SESSION['oldInput'] = $req->getParams();
    
    // call next
    $res = $next($req, $res);
    return $res;
  }
}