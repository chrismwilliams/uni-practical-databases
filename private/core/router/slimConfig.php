<?php 
// main slim configuration file

// slim settings *** SET TO false in production ***
$config['displayErrorDetails'] = true;

// slim config
$config['addContentLenghHeader'] = false;

// admin and logged In check before certain routes
$config['determineRouteBeforeAppMiddleware'] = true;

// setup router
$app = new \Slim\App(["settings" => $config]);


// get the slim container
$container = $app->getContainer();

// add custom classes to container
$container['Staff'] = function ($c) {
  return new App\Models\Staff;
};
$container['Customer'] = function ($c) {
  return new App\Models\Customer;
};

// Add the Views template engine Twig
$container['view'] = function ($c) {
  $view = new \Slim\Views\Twig(__DIR__ . '/../../../src/views', [
    'cache' => false
  ]);

  $view->addExtension(new \Slim\Views\TwigExtension(
    $c->router,
    $c->request->getUri()
  ));

  $view->getEnvironment()->addGlobal('staff', $c->Staff);
  $view->getEnvironment()->addGlobal('cust', $c->Customer);

  return $view;
};

// 404 handler
$container['notFoundHandler'] = function ($c) {
  return function ($request, $response) use ($c) {
    return $c['view']->render($response->withStatus(404), '404.view.twig');
  };
};



// Controllers to handle requests
$container['PageController'] = function ($c) {
  return new App\Controllers\PageController($c);
};
$container['AuthController'] = function ($c) {
  return new App\Controllers\AuthController($c);
};
$container['AdminController'] = function ($c) {
  return new App\Controllers\AdminController($c);
};
$container['ApiController'] = function ($c) {
  return new App\Controllers\ApiController($c);
};
$container['MovieController'] = function ($c) {
  return new App\Controllers\MovieController($c);
};

// add validation to container
$container['validator'] = function ($c) {
  return new App\Helpers\Validator;
};

// add middleware
$app->add(new App\Middleware\ErrorsMiddleware($container));
$app->add(new App\Middleware\InputMiddleware($container));
$app->add(new App\Middleware\FlashMiddleware($container));