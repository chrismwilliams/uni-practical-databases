<?php

// Class to handle the authorising of a route based on the user

namespace App\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AuthRouteMiddleware extends Middleware {

  public function __invoke(Request $req, Response $res, $next) {
    // get the route name
    $route = $req->getAttribute('route');
    $routeName = $route->getName();

    // admin routes
    $adminRoutes = [
      'addStaff',
      'editStaff',
      'editStaffByID',
      'postAddStaff',
      'postUpdateStaff',
      'addMovie',
      'editMovieByID',
      'postAddMovie',
      'postUpdateMovie',
      'orders'
    ];
    // first check if they're logged-in
    if(!is_logged_in()) {
      // redirect to login if they're not
      $res = $res->withRedirect($this->container->router->pathFor('login'));

    // check if they're trying to access an admin route and are an admin
    } elseif(!is_admin() && in_array($routeName, $adminRoutes)) {
      
      // if not redirect to home
      $res = $res->withRedirect($this->container->router->pathFor('index'));
      
    } else {
      // authorised so call next
      $res = $next($req, $res);
    }

    return $res;
  }
}