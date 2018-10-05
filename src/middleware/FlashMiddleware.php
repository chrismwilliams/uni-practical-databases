<?php

// middleware to handle flash messages

namespace App\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class FlashMiddleware extends Middleware {

  public function __invoke(Request $req, Response $res, $next) {

    // check if the session['flash'] is set
    if(isset($_SESSION['flash'])) {

      // if true set the flash and message to the view
      $this->container->view->getEnvironment()->addGlobal('flash', $_SESSION['flash']);
      // remove the flash from the next request
      unset($_SESSION['flash']);
    }
    
    // call next
    $res = $next($req, $res);
    return $res;
  }
}