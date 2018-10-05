<?php
// main system wide functions page

// function to die and var dump values/variables, used in testing
function dd($value)
{
  die(var_dump($value));
}
// another testing function to dump in json format
function ddjson($value)
{
  die(json_encode($value));
}
// escape dodgy html from the user
function safe_html($html)
{
  return htmlspecialchars($html);
}
// check if value ($val) is set
function val_set($val)
{
  return isset($val) && !empty($val) && trim($val) !== '';
}
// hash the password
function hash_password($password)
{
  return password_hash($password, PASSWORD_BCRYPT);
}
// check if the user is logged in by checking the session global
function is_logged_in()
{
  return isset($_SESSION['user_id']);
}
// check if the user is an admin through the use of sessions
function is_admin()
{
  return (is_logged_in() && $_SESSION['user_type'] && $_SESSION['user_type'] === 'admin');
}
// set the session and log the user in
function log_in_user($user)
{
  session_regenerate_id();
  $_SESSION['user_id'] = $user['id'];
  $_SESSION['user_type'] = $user['type'];
  $_SESSION['username'] = $user['username'];
}
// log out the user and unset the session
function log_out()
{
  unset($_SESSION['user_id']);
  unset($_SESSION['user_type']);
  unset($_SESSION['username']);

  remove_user();
  unset($_SESSION['movie']);
  // session_destroy();
}
// add customer to session
function add_user($user)
{
  $_SESSION['cust_id'] = $user['id'];
  $_SESSION['cust_name'] = $user['name'];
}
// update the user if set in session
function update_user($user)
{
  if(isset($_SESSION['cust_id'])) {
    $_SESSION['cust_name'] = $user;
  }
}
// remove customer from session
function remove_user()
{
  unset($_SESSION['cust_id']);
  unset($_SESSION['cust_name']);
}
// function to add a movie to session, specifically the movie_productID
function add_movie($productID)
{
  $_SESSION['movie'][] = $productID;
}
// function to remove movie from the session, checks if it is in session as a sanity check
function remove_movie($movieProductID)
{
  if(array_search($movieProductID, $_SESSION['movie']) !== false) {
    unset($_SESSION['movie'][array_search($movieProductID, $_SESSION['movie'])]);
    $_SESSION['movie'] = array_values($_SESSION['movie']);
  }
}

// redirect the user to the location passed, mainly used in testing
function redirect_to($location)
{
  header("Location: {$location}");

  exit();
}
// check if it is a post request, mainly used in testing
function is_post_request()
{
  return $_SERVER['REQUEST_METHOD'] == 'POST';
}

