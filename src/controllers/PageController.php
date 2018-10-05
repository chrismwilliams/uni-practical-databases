<?php

// controller to handle main pages of application, including the customer

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;
use App\Core\Database\DB;
use App\Models\Customer;


class PageController extends Controller
{
  
  // function to load the 404 page 
  public function page404(Request $req, Response $res)
  {
    $this->loadView($res, '404');
  }

  // function to load the home page
  public function homePage(Request $req, Response $res)
  {
    $this->loadView($res, 'index');
  }

  // function to load the add customer view
  public function addCustomer(Request $req, Response $res)
  {

    // need to get customers to add a ref?
    $this->loadView($res, 'addCustomer');
  }

  // handle the posted customer to the database
  public function postAddCustomer(Request $req, Response $res)
  {
    // Validate posted values
    $validation = $this->container->validator->checkValues($req, [
      'fname' => v::notEmpty()->alpha(),
      'sname' => v::notEmpty()->alpha(),
      'address' => v::notEmpty()->alnum(),
      'postCode' => v::notEmpty()->alnum(),
      'tel' => v::notEmpty()->phone(),
      'email' => v::notEmpty()->email()
    ]);

    // check errors array and redirect if error(s)
    if (!empty($validation->errorsArray())) {
      $_SESSION['errors'] = $validation->errorsArray();
      return $res->withRedirect($this->container->router->pathFor(addCustomer));
    }
    
    // get values
    $data = $req->getParsedBody();

    $fname = $data['fname'];
    $sname = $data['sname'];
    $address = trim($data['address']);
    $postCode = trim($data['postCode']);
    $tel = $data['tel'];
    $email = trim($data['email']);

    // check if they are in the db already
    $customerExists = DB::checkIfCustomer(array('fname' => $fname, 'sname' => $sname, 'postCode' => $postCode, 'email' => $email))[0];

    if (sizeof($customerExists) > 0) {
      // show flash as the customer already registered
      unset($_SESSION['oldInput']);
      $_SESSION['flash'] = array('key' => 'error', 'msg' => 'Customer already registered');
      return $res->withRedirect($this->container->router->pathFor('customerByID', array('id' => $customerExists['customerID'])));
    }

    // check valid reference no set
    $ref = val_set($data['cust_ref']) ? $data['cust_ref'] : null;

    // enter details to db
    $newID = DB::insert('customer', ['fname' => $fname, 'sname' => $sname, 'address' => $address, 'postCode' => $postCode, 'tel' => $tel, 'email' => $email, 'ref' => $ref]);

    // redirect and clear values
    unset($_SESSION['oldInput']);
    $_SESSION['flash'] = array('key' => 'success', 'msg' => 'Customer Added!');
    return $res->withRedirect($this->container->router->pathFor('customerByID', array('id' => $newID)));
  }

  // method to select a customer and add to the current session
  public function postSelectCustomer(Request $req, Response $res)
  {
    // get and set the passed data
    $data = $req->getParsedBody();

    $custID = $data['customerID'];
    $custName = $data['customerName'];

    // set customer in session
    add_user(['id' => $custID, 'name' => $custName]);

    // reload route
    unset($_SESSION['oldInput']);
    $_SESSION['flash'] = array('key' => 'success', 'msg' => 'Customer Selected!');
    return $res->withRedirect($this->container->router->pathFor('customerByID', array('id' => $custID)));
  }

  // method to remove the current user from the session
  public function postDeselectCustomer(Request $req, Response $res)
  {
    // unset customer
    remove_user();

    // set the flash msg and re-load index page
    $_SESSION['flash'] = array('key' => 'success', 'msg' => 'Customer De-selected!');
    return $res->withRedirect($this->container->router->pathFor('index'));
  }

  // function to load the search customer page
  public function searchCustomer(Request $req, Response $res)
  {
    $this->loadView($res, 'searchCustomer');
  }

  // function to load a particular customer
  public function customerByID(Request $req, Response $res, $args)
  {
    // find the customer by the id
    $result = DB::findCustomerByID($args['id']);

    // if found, load the customer's page with details and orders
    if (sizeof($result) > 0) {
      $result = $result[0];
      $orders = DB::customerOrders($args['id']);
      $this->loadView($res, 'customer', ['customer' => $result, 'orders' => $orders]);
    } else {
      // show error no customer with this ID
      $_SESSION['flash'] = array('key' => 'error', 'msg' => 'Customer not found!');
      return $res->withRedirect($this->container->router->pathFor('searchCustomer'));
    }
  }

  // method to load the edit customer page
  public function editCustomerByID(Request $req, Response $res, $args)
  {
    // find the customer in the db
    $result = DB::findCustomerByID($args['id']);

    // if a result
    if (sizeof($result) > 0) {
      $result = $result[0];
      $this->loadView($res, 'editCustomer', ['customer' => $result]);
    } else {
      // show error no customer with this ID
      $_SESSION['flash'] = array('key' => 'error', 'msg' => 'Customer not found!');
      return $res->withRedirect($this->container->router->pathFor('searchCustomer'));
    }
  }

  // method to update the customers details
  public function postUpdateCustomer(Request $req, Response $res, $args)
  {
    // Validate posted values
    $validation = $this->container->validator->checkValues($req, [
      'fname' => v::notEmpty()->alpha(),
      'sname' => v::notEmpty()->alpha(),
      'address' => v::notEmpty()->alnum(),
      'postCode' => v::notEmpty()->alnum(),
      'email' => v::notEmpty()->email(),
      'tel' => v::notEmpty()->phone()
    ]);

    // check errors array and redirect if error(s)
    if (!empty($validation->errorsArray())) {
      $_SESSION['errors'] = $validation->errorsArray();
      return $res->withRedirect($this->container->router->pathFor(addCustomer));
    }
    
    // get values
    $data = $req->getParsedBody();

    $fname = $data['fname'];
    $sname = $data['sname'];
    $address = trim($data['address']);
    $postCode = trim($data['postCode']);
    $email = trim($data['email']);
    $tel = $data['tel'];

    // update customer with new details
    DB::updateCustomer($args['id'], array('fname' => $fname, 'sname' => $sname, 'address' => $address, 'postCode' => $postCode, 'email' => $email, 'tel' => $tel));

    // unset the old customer inputs
    unset($_SESSION['oldInput']);

    // update session user if set in session
    update_user($fname . ' ' . $sname);

    // show success flash and redirect
    $_SESSION['flash'] = array('key' => 'success', 'msg' => 'Customer Updated!');
    return $res->withRedirect($this->container->router->pathFor('customerByID', array('id' => $args['id'])));
  }

  // method to return a movie for a particular customer & order
  public function postRtnMovie(Request $req, Response $res, $args)
  {
    // get id of customer
    $data = $req->getParsedBody();
    $id = $data['customerID'];
    
    // update the db
    DB::rtnMovie((int)$args['id']);

    // unset the old customer inputs
    unset($_SESSION['oldInput']);

    // show success flash and redirect
    $_SESSION['flash'] = array('key' => 'success', 'msg' => 'Movie Returned');
    return $res->withRedirect($this->container->router->pathFor('customerByID', array('id' => $id)));
  }

  // method for loading the late return of a movie
  public function customerRtnLateMovie(Request $req, Response $res, $args)
  {
    // find the late return details in db
    $result = DB::custLateRtn($args['rntID']);
    if (sizeof($result) > 0) {
      $result = $result[0];

      // check if its been returned already
      if ($result['returned']) {
        $_SESSION['flash'] = array('key' => 'error', 'msg' => 'Order already returned!');
        return $res->withRedirect($this->container->router->pathFor('customerByID', array('id' => $args['id'])));
      }

      // check if it is late and the from the current customer
      if (date("Y-m-d") > $result['returnDate'] && (int)$result['customerID'] === (int)$args['id']) {
        $this->loadView($res, 'returnMovie', ['customer' => $result]);
      } else {
        // show error as order isn't late
        $_SESSION['flash'] = array('key' => 'error', 'msg' => 'Order not late!');
        return $res->withRedirect($this->container->router->pathFor('customerByID', array('id' => $args['id'])));
      }

    } else {
      // show error no Order with this ID
      $_SESSION['flash'] = array('key' => 'error', 'msg' => 'Order not found!');
      return $res->withRedirect($this->container->router->pathFor('customerByID', array('id' => $args['id'])));
    }
  }

  // method to return all the customer who've been referred and by whom
  public function referrers(Request $req, Response $res)
  {
    $result = DB::allReferrers();
    if (sizeof($result) > 0) {
      $this->loadView($res, 'referrers', ['customers' => $result]);
    } else {
      // show error referrers not found
      $_SESSION['flash'] = array('key' => 'error', 'msg' => 'List of referrers not found!');
      return $res->withRedirect($this->container->router->pathFor('index'));
    }
  }

  // method to load the orders returned from the db
  public function orders(Request $req, Response $res)
  {
    $result = DB::orders();
    if (sizeof($result) > 0) {
      $this->loadView($res, 'orders', ['orders' => $result]);
    } else {
      // show error orders not found
      $_SESSION['flash'] = array('key' => 'error', 'msg' => 'List of orders not found!');
      return $res->withRedirect($this->container->router->pathFor('index'));
    }
  }

  // method to add a movie to the current session
  public function postAddToOrder(Request $req, Response $res)
  {
    // get movie product ID
    $data = $req->getParsedBody();

    $movieID = $data['movieID'];
    $movieProductID = $data['movieProductID'];

    // add product ID to session
    add_movie($movieProductID);

    // load the movie pg again
    unset($_SESSION['oldInput']);

    $_SESSION['flash'] = array('key' => 'success', 'msg' => 'Movie Added');
    return $res->withRedirect($this->container->router->pathFor('movieByID', array('id' => $movieID)));
  }

  // method to remove a movie from the current session
  public function postRemoveFromOrder(Request $req, Response $res)
  {
    // get the movieProductID
    $data = $req->getParsedBody();

    remove_movie($data['movieProductID']);

    // flash a success msg and reload the index pg ***** could load the page again *****
    $_SESSION['flash'] = array('key' => 'success', 'msg' => 'Movie Removed From Order');
    return $res->withRedirect($this->container->router->pathFor('orderMovie'));
  }

  // method to show the current order and customer details
  public function orderMovie(Request $req, Response $res)
  {
    // first check if movie(s) set
    if (!isset($_SESSION['movie']) || empty($_SESSION['movie'])) {
      
      // show an error and redirect as it's not
      $_SESSION['flash'] = array('key' => 'error', 'msg' => 'Please Add A Movie!');
      return $res->withRedirect($this->container->router->pathFor('searchMovie'));

    // check if a customer has been selected
    } elseif (!isset($_SESSION['cust_id']) || empty($_SESSION['cust_id'])) {

      // show an error and redirect with msg
      $_SESSION['flash'] = array('key' => 'error', 'msg' => 'Please Select A Customer!');
      return $res->withRedirect($this->container->router->pathFor('searchCustomer'));

    } else {

      // first find the customer in the db
      $result = DB::findCustomerByID($_SESSION['cust_id']);
      if (sizeof($result) > 0) {
        $result = $result[0];

        // store and find movie(s) in session
        $movieProductIDs = $_SESSION['movie'];
        
        // find all the movie details
        $movieDetails = DB::getMovieDetails($movieProductIDs);

        // load the order view
        $this->loadView($res, 'order', ['customer' => $result, 'movieDetails' => $movieDetails]);

      } else {
        // show error no customer with this ID
        $_SESSION['flash'] = array('key' => 'error', 'msg' => 'Customer not found!');
        return $res->withRedirect($this->container->router->pathFor('searchCustomer'));
      }
    }
  }

  // method to process and order a movie, checks if a movie & customer is added before proceeding
  public function postOrderMovie(Request $req, Response $res)
  {
    // first check if movie(s) set in session
    if (!isset($_SESSION['movie']) || empty($_SESSION['movie'])) {

      // movie not added
      $_SESSION['flash'] = array('key' => 'error', 'msg' => 'Please Add A Movie!');
      return $res->withRedirect($this->container->router->pathFor('searchMovie'));
    
    // check if customer set also
    } elseif (!isset($_SESSION['cust_id']) || empty($_SESSION['cust_id'])) {

      // customer not added
      $_SESSION['flash'] = array('key' => 'error', 'msg' => 'Please Select A Customer!');
      return $res->withRedirect($this->container->router->pathFor('searchCustomer'));

    }

    // get the data
    $data = $req->getParsedBody();
    // length of rental
    $length = $data['period'];

    // todays date
    $date = date("Y-m-d");
    // add length of rental to todays date
    $inc_date = date("Y-m-d", strtotime($date . $length . " days"));

    // order the movie with data stored
    DB::makeCustomerOrder(array('orderDate' => date("Y-m-d"), 'returnDate' => $inc_date, 'customerID' => (int)$_SESSION['cust_id'], 'staffID' => $_SESSION['user_id']));

    // unset customer and all movies in session
    unset($_SESSION['movie']);
    remove_user();
    unset($_SESSION['oldInput']);

    // order complete, return to home page
    $_SESSION['flash'] = array('key' => 'success', 'msg' => 'Order Complete');
    return $res->withRedirect($this->container->router->pathFor('index'));
  }
}