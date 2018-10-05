<?php

// base controller that store the slim container and template engine twig for handling views

namespace App\Controllers;

use \Interop\Container\ContainerInterface;
use \Psr\Http\Message\ResponseInterface as Response;

abstract class Controller {

  protected $container;
  protected $view;

  public function __construct(ContainerInterface $container) {
    $this->container = $container;
    $this->view = $container->view;
  }

  // function to load a twig view with the data if passed
  public function loadView(Response $res, $view, $data = []) {
    // set data into values
    extract($data);
    // load the view with the data
    $res = $this->view->render($res, "{$view}.view.twig", $data);

    return $res;
  }
}