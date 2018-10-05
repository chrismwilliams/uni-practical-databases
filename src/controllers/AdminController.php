<?php

// admin controller that handles get & post routes for admins

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;
use App\Core\Database\DB;

class AdminController extends Controller
{
  
  // function to add new staff
  public function addStaff(Request $req, Response $res)
  {

    $result = DB::selectAll('access');

    $this->loadView($res, 'staff/addStaff', ['access_levels' => $result]);

  }

  // function to handle post staff values
  public function postAddStaff(Request $req, Response $res)
  {

    // Validate posted values
    $validation = $this->container->validator->checkValues($req, [
      'fname' => v::notEmpty()->alpha()->noWhitespace(),
      'sname' => v::notEmpty(),
      'address' => v::notEmpty(),
      'access' => v::notEmpty(),
      'username' => v::notEmpty()->noWhitespace()->length(6, 30),
      'password' => v::notEmpty()->noWhitespace()->length(6, 30)
    ]);

    // check errors array and redirect if error(s)
    if (!empty($validation->errorsArray())) {
      $_SESSION['errors'] = $validation->errorsArray();
      return $res->withRedirect($this->container->router->pathFor(addStaff));
    }
    
    // get values
    $data = $req->getParsedBody();

    $fname = $data['fname'];
    $sname = trim($data['sname']);
    $address = trim($data['address']);
    $accessID = $data['access'];
    $username = $data['username'];
    $password = $data['password'];
        
    
    // check username used
    if (DB::usernameTaken($username)) {
      $_SESSION['errors'] = array('error' => ['Username already taken']);
      return $res->withRedirect($this->container->router->pathFor(addStaff));
    }
    // hash password  
    $password = hash_password($password);
    
    // save staff member
    DB::insert('staff', ['fname' => $fname, 'sname' => $sname, 'address' => $address, 'accessID' => $accessID, 'username' => $username, 'password' => $password]);
    
    // redirect and clear values
    unset($_SESSION['oldInput']);
    $_SESSION['flash'] = array('key' => 'success', 'msg' => 'Staff Member Added!');
    return $res->withRedirect($this->container->router->pathFor(addStaff));
  }

  // function to get all the app's staff members
  public function editStaff(Request $req, Response $res)
  {
    
    // Get all the staff details
    $result = DB::selectAllStaff();

    $this->loadView($res, 'staff/editStaff', ['allStaff' => $result]);
  }

  // get all the details for a staff member 
  public function editStaffMember(Request $req, Response $res, $args)
  {
    
    // Get staff details
    $result = DB::staffDetails($args['id']);
    $ac_levels = DB::selectAll('access');

    if (sizeof($result) > 0) {
      $result = $result[0];
      $this->loadView($res, 'staff/editMember', ['member' => $result, 'accessLevels' => $ac_levels]);
    } else {
      // show error no staff with this ID
      $_SESSION['flash'] = array('key' => 'error', 'msg' => 'StaffID does not exist');
      return $res->withRedirect($this->container->router->pathFor('editStaff'));
    }
  }

  // handle updating a single staff member
  public function postUpdateStaff(Request $req, Response $res, $args)
  {
    
    // Validate posted values
    $validation = $this->container->validator->checkValues($req, [
      'fname' => v::notEmpty()->alpha()->noWhitespace(),
      'sname' => v::notEmpty(),
      'address' => v::notEmpty(),
      'access' => v::notEmpty()
    ]);

    // check errors array and redirect if error(s)
    if (!empty($validation->errorsArray())) {
      $_SESSION['errors'] = $validation->errorsArray();
      return $res->withRedirect($this->container->router->pathFor(editStaff));
    }
    
    // get values
    $data = $req->getParsedBody();

    $id = $args['id'];
    $fname = $data['fname'];
    $sname = trim($data['sname']);
    $address = trim($data['address']);
    $accessID = $data['access'];
    $password = isset($data['password']) ? $data['password'] : '';

    // check if password provided and check value if it is
    if (val_set($password)) {
      $validation->checkValues($req, [
        'password' => v::noWhitespace()->length(6, 30)
      ]);

      // check errors array and redirect if error(s)
      if (!empty($validation->errorsArray())) {
        $_SESSION['errors'] = $validation->errorsArray();
        return $res->withRedirect($this->container->router->pathFor(editStaff));
      }

      // Update staff password
      // hash password  
      $password = hash_password($password);
      DB::updateStaffPassword($id, $password);
    }

    // update staff details
    DB::updateStaff($id, ['fname' => $fname, 'sname' => $sname, 'address' => $address, 'accessID' => $accessID]);

    // redirect with success
    $_SESSION['flash'] = array('key' => 'success', 'msg' => 'Staff Updated!');
    return $res->withRedirect($this->container->router->pathFor(editStaff));

  }

  // remove a staff member from the database
  public function postDeleteStaff(Request $req, Response $res, $args)
  {
    // don't allow admin user to delete themselves
    if ($_SESSION['user_id'] == $args['id']) {
      $_SESSION['flash'] = array('key' => 'error', 'msg' => 'Couldn\'t Delete Staff Member!');
      return $res->withRedirect($this->container->router->pathFor(editStaff));
    }

    if (DB::deleteStaffMember($args['id'])) {
      // redirect with success
      $_SESSION['flash'] = array('key' => 'success', 'msg' => 'Staff Member Deleted!');
      return $res->withRedirect($this->container->router->pathFor(editStaff));
    } else {
      $_SESSION['flash'] = array('key' => 'error', 'msg' => 'Couldn\'t Delete Staff Member!');
      return $res->withRedirect($this->container->router->pathFor(editStaff));
    }
  }
}