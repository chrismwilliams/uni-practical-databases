<?php
// class used by twig to render specfic elements
namespace App\Models;

class Staff
{
  // method to check if a user is logged-in
  public function isLoggedIn()
  {
    return isset($_SESSION['user_id']);
  }

  // method to check if they are an admin
  public function isAdmin()
  {
    return ($this->isLoggedIn() && $_SESSION['user_type'] && $_SESSION['user_type'] === 'admin');
  }

  // method to return the staff's username
  public function userName()
  {
    return isset($_SESSION['username']) ? $_SESSION['username'] : '';
  }

  // method to return the staff's id, if it is set
  public function userID()
  {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';
  }
}