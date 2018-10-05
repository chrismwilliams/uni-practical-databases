<?php
// customer class that is used by twig to determine rendering of elemenets
namespace App\Models;

class Customer
{
  // function to check if a customer is added to the session
  public function isAdded()
  {
    return isset($_SESSION['cust_id']);
  }

  // method to return the customers name if set
  public function custName()
  {
    return isset($_SESSION['cust_name']) ? $_SESSION['cust_name'] : '';
  }

  // method to return the customer's id if set
  public function custID()
  {
    return isset($_SESSION['cust_id']) ? $_SESSION['cust_id'] : '';
  }

  // method to return if the movie session set
  public function currentOrder()
  {
    return isset($_SESSION['movie']) ? $_SESSION['movie'] : '';
  }

  // method to rtn the total number of orders
  public function orderTotal()
  {
    if(isset($_SESSION['movie'])) {
      return count($_SESSION['movie']);
    } else {
      return 0;
    }
  }
}