<?php

// all the get routes the application handles

use App\Middleware\AuthRouteMiddleware;
use App\Middleware\GuestRouteMiddleware;

// home routes that are accessible to any user
$app->get('/', 'PageController:homePage')->setName('index');
$app->get('/index', 'PageController:homePage')->setName('index');

// routes that a logged-in user shouldn't be able to access
$app->group('', function () {
  // login route
  $this->get('/login', 'AuthController:loginPage')->setName('login');
// guest middleware 
})->add(new GuestRouteMiddleware($container));

// group of get routes that are handled by authroutemiddleware to determine the access level required for the route
$app->group('', function () {
  // logout route
  $this->get('/logout', 'AuthController:logOut')->setName('logout');
  
  // Customer routes
  $this->get('/add_customer', 'PageController:addCustomer')->setName('addCustomer');
  $this->get('/referrers', 'PageController:referrers')->setName('referrers');
  $this->get('/orders', 'PageController:orders')->setName('orders');
  $this->get('/search_customer', 'PageController:searchCustomer')->setName('searchCustomer');
  $this->get('/customer/{id:[0-9]+}', 'PageController:customerByID')->setName('customerByID');
  $this->get('/edit_customer/{id:[0-9]+}', 'PageController:editCustomerByID')->setName('editCustomerByID');
  $this->get('/cust_rtn_movie/{id:[0-9]+}/{rntID:[0-9]+}', 'PageController:customerRtnLateMovie')->setName('customerRtnLateMovie');
  $this->get('/order', 'PageController:orderMovie')->setName('orderMovie');
  
  // Movie routes
  $this->get('/movie/{id:[0-9]+}', 'MovieController:movieByID')->setName('movieByID');
  $this->get('/movie_search', 'MovieController:movieSearch')->setName('searchMovie');
  $this->get('/add_movie', 'MovieController:addMovie')->setName('addMovie');
  $this->get('/edit_movie/{id:[0-9]+}', 'MovieController:editMovieByID')->setName('editMovieByID');
  
  // Admin routes
  $this->get('/add_staff', 'AdminController:addStaff')->setName('addStaff');
  $this->get('/edit_staff', 'AdminController:editStaff')->setName('editStaff');
  $this->get('/edit_staff/{id:[0-9]+}', 'AdminController:editStaffMember')->setName('editStaffByID');
// auth middleware
})->add(new AuthRouteMiddleware($container));



