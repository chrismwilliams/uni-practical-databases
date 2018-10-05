<?php

// middleware to handle a guest login

namespace App\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class GuestRouteMiddleware extends Middleware {

  public function __invoke(Request $req, Response $res, $next) {

    // if they are logged-in, redirect to the index page as they're trying to access the login page
    if(is_logged_in()) {
      return $res->withRedirect($this->container->router->pathFor('index'));
    }
    // return next
    return $res = $next($req, $res);
  }
}