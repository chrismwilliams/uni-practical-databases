<?php

// post routes the application serves

use App\Middleware\AuthRouteMiddleware;
use App\Middleware\GuestRouteMiddleware;

// group of routes a logged-in user shouldn't reach
$app->group('', function () {
  // post user login
  $this->post('/user_login', 'AuthController:postUserLogin')->setName('postLogin');
})->add(new GuestRouteMiddleware($container));

// group of post routes that are handled by authroutemiddleware to determine the access level required
$app->group('', function () {

  // Customer post routes
  $this->post('/add_customer', 'PageController:postAddCustomer')->setName('postAddCustomer');
  $this->post('/select_customer', 'PageController:postSelectCustomer')->setName('postSelectCustomer');
  $this->post('/deselect_customer', 'PageController:postDeselectCustomer')->setName('postDeselectCustomer');
  $this->post('/update_customer/{id:[0-9]+}', 'PageController:postUpdateCustomer')->setName('postUpdateCustomer');
  $this->post('/rtnMovie/{id:[0-9]+}', 'PageController:postRtnMovie')->setName('postRtnMovie');
  $this->post('/add_to_order', 'PageController:postAddToOrder')->setName('postAddToOrder');
  $this->post('/rm_from_order', 'PageController:postRemoveFromOrder')->setName('postRemoveFromOrder');
  $this->post('/orderMovie', 'PageController:postOrderMovie')->setName('postOrderMovie');
  
  // Movie post routes
  $this->post('/add_movie', 'MovieController:postAddMovie')->setName('postAddMovie');
  $this->post('/update_movie/{id:[0-9]+}', 'MovieController:postUpdateMovie')->setName('postUpdateMovie');
  
  
  // Admin post routes
  $this->post('/add_staff', 'AdminController:postAddStaff')->setName('postAddStaff');
  $this->post('/update_staff/{id:[0-9]+}', 'AdminController:postUpdateStaff')->setName('postUpdateStaff');
  $this->post('/delete_staff/{id:[0-9]+}', 'AdminController:postDeleteStaff')->setName('postDeleteStaff');

// add the auth middleware
})->add(new AuthRouteMiddleware($container));