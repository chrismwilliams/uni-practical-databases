<?php

// base middleware class to store the slim container

namespace App\Middleware;

use \Interop\Container\ContainerInterface;


abstract class Middleware {

  protected $container;

  // store slim container
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }
}