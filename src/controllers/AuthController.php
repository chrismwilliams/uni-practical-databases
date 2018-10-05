<?php

// auth controller to handle user actions, login & logout

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;
use App\Core\Database\DB;

class AuthController extends Controller
{

  // function to get the login page
  public function loginPage(Request $req, Response $res)
  {
    $this->loadView($res, 'login');
  }

  // function to handle the posted user login details
  public function postUserLogin(Request $req, Response $res)
  {
    // validate username & password
    $validation = $this->container->validator->checkValues($req, [
      'username' => v::noWhitespace()->notEmpty(),
      'password' => v::noWhitespace()->notEmpty()
    ]);

    if (!empty($validation->errorsArray())) {
      $_SESSION['errors'] = $validation->errorsArray();
      return $res->withRedirect($this->container->router->pathFor(login));
    }

    $data = $req->getParsedBody();

    $username = isset($data['username']) ? $data['username'] : '';
    $password = isset($data['password']) ? $data['password'] : '';
  
    // verify user details
    $result = DB::verify($username, $password);

    // if true
    if ($result) {
      // successful login
      log_in_user(['id' => $result['staffID'], 'type' => $result['name'], 'username' => $result['username']]);
      $_SESSION['flash'] = array('key' => 'success', 'msg' => 'Welcome, You are now logged in!');
      return $res->withRedirect($this->container->router->pathFor(index));;

    } else {
      // set errors in session
      $_SESSION['flash'] = array('key' => 'error', 'msg' => 'Incorrect Username or Password');
      return $res->withRedirect($this->container->router->pathFor(login));
    }
  }

  // function to log the user out
  public function logOut(Request $req, Response $res)
  {
    // call functions.php log_out
    log_out();
    // show success flash and redirect
    $_SESSION['flash'] = array('key' => 'success', 'msg' => 'You are now logged out!');
    return $res->withRedirect($this->container->router->pathFor(index));
  }
}