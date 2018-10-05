<?php

// controller to handle all movie actions/functionality

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;
use App\Core\Database\DB;

class MovieController extends Controller
{
  
  // function to return the movie search page
  public function movieSearch(Request $req, Response $res)
  {

    $this->loadView($res, 'movieSearch');
  }

  // function to return the individual movie by the id
  public function movieByID(Request $req, Response $res, $args)
  {
    // find the movie in the db
    $result = DB::findMovieByID($args['id']);
    // if there's a result base64 the image and load the view
    if (sizeof($result) > 0) {
      $result['movie'] = $result['movie'][0];
      $result['movie']['img'] = base64_encode($result['movie']['img']);
      $this->loadView($res, 'movie', ['movie' => $result['movie'], 'platformDetails' => $result['platform']]);
    } else {
      // show error no movie with this ID
      $_SESSION['flash'] = array('key' => 'error', 'msg' => 'Movie does not exist');
      return $res->withRedirect($this->container->router->pathFor('searchMovie'));
    }
  }

  // function to get the form to add a movie
  public function addMovie(Request $req, Response $res)
  {
    
    // get all genres
    $genres = DB::selectAll('genre');

    // load form for movie
    $this->loadView($res, 'addMovie', ['genres' => $genres]);
  }

  // function to handle the posted data to add a movie
  public function postAddMovie(Request $req, Response $res)
  {
    
    // Validate posted values
    $validation = $this->container->validator->checkValues($req, [
      'name' => v::notEmpty(),
      'desc' => v::notEmpty(),
      'genre' => v::notEmpty(),
      'platform' => v::notEmpty()
    ]);
      
      // check errors array and redirect if error(s)
    if (!empty($validation->errorsArray())) {
      $_SESSION['errors'] = $validation->errorsArray();
      return $res->withRedirect($this->container->router->pathFor('addMovie'));
    }
    
    // set errors array
    $errors = [];

    // store data
    $data = $req->getParsedBody();

    $name = trim($data['name']);
    $desc = trim($data['desc']);
    $genre = $data['genre'];
    $genre = $genre === 'null' ? NULL : $genre;
    $img = $req->getUploadedFiles();
    $img = $img['img'];
    $platforms = $data['platform'];
    $platformData = [];

    // check image added
    if ($img->getError() !== UPLOAD_ERR_OK) {
      $errors['Image'] = ["Image Upload Failed"];
    }

    // check per platform & values correct format
    foreach ($platforms as $key => $plat) {
      $data[strtolower($plat) . '_cost'] <= 0.00 ? $errors['Cost'] = [$plat . " cost must be greater than 0.00"] : $platformData[$plat]['cost'] = floatval($data[strtolower($plat) . '_cost']);
      $data[strtolower($plat) . '_lateFee'] <= 0.00 ? $errors['Late Fee'] = [$plat . " late Fee must be greater than 0.00"] : $platformData[$plat]['lateFee'] = floatval($data[strtolower($plat) . '_lateFee']);
      $data[strtolower($plat) . '_quantity'] <= 0 ? $errors['Quantity'] = [$plat . " quantity must be greater than 0"] : $platformData[$plat]['quantity'] = intval($data[strtolower($plat) . '_quantity']);
    }

    // check film not already stored
    if (DB::findMovieByTitle($name)) {
      $errors['Title'] = ["Movie already exists in database!"];
    }

    // if errors array empty, add to db
    if (empty($errors)) {

      // upload movie as a BLOB
      $imgType = pathinfo($img->getClientFilename(), PATHINFO_EXTENSION);
      $imgBlob = $img->file;
      $fp = fopen($imgBlob, 'rb');
      DB::addNewMovie($fp, $imgType, array('title' => $name, 'description' => $desc, 'genre' => $genre, 'platformData' => $platformData));
      fclose($fp);

    } else {
      // set errors in session
      $_SESSION['errors'] = $errors;
      return $res->withRedirect($this->container->router->pathFor('addMovie'));
    }
    
    // unset the old movie inputs
    unset($_SESSION['oldInput']);

    // show success flash and redirect
    $_SESSION['flash'] = array('key' => 'success', 'msg' => 'Movie Added!');
    return $res->withRedirect($this->container->router->pathFor('addMovie'));
  }

  // function to edit a single movie
  public function editMovieByID(Request $req, Response $res, $args)
  {
    // find the movie in the db
    $result = DB::findMovieByID($args['id']);
    // if found, load the edit view
    if (sizeof($result) > 0) {
      $result = $result['movie'][0];
      $result['img'] = base64_encode($result['img']);
      
      // get genres
      $genres = DB::selectAll('genre');

      $this->loadView($res, 'editMovie', ['movie' => $result, 'genres' => $genres]);
    } else {
      // show error no movie with this ID
      $_SESSION['flash'] = array('key' => 'error', 'msg' => 'Movie does not exist');
      return $res->withRedirect($this->container->router->pathFor('searchMovie'));
    }
  }

  // function to handle the posted new values of a movie
  public function postUpdateMovie(Request $req, Response $res, $args)
  {
    // Validate posted values
    $validation = $this->container->validator->checkValues($req, [
      'name' => v::notEmpty(),
      'desc' => v::notEmpty(),
      'genre' => v::notEmpty(),
    ]);

    // check errors array and redirect if error(s)
    if (!empty($validation->errorsArray())) {
      $_SESSION['errors'] = $validation->errorsArray();
      return $res->withRedirect($this->container->router->pathFor('editMovieByID', array('id' => $args['id'])));
    }

    // store the data
    $data = $req->getParsedBody();

    $name = $data['name'];
    $desc = $data['desc'];
    $genre = $data['genre'];
    $genre = $genre === 'null' ? NULL : $genre;
    $img = $req->getUploadedFiles();
    $img = $img['img'];
    $movieID = $args['id'];

    // check if image
    if ($img->getError() === UPLOAD_ERR_OK) {
      // get image
      $imgType = pathinfo($img->getClientFilename(), PATHINFO_EXTENSION);
      $imgBlob = $img->file;

      // upload movie img first
      $fp = fopen($imgBlob, 'rb');
      DB::updateMovieImg($fp, $imgType, $movieID);
      fclose($fp);
    }

    // update movie with new details
    DB::updateMovie(array('movieID' => $movieID, 'title' => $name, 'desc' => $desc, 'genre' => $genre));
    
    // unset the old movie inputs
    unset($_SESSION['oldInput']);

    // show success flash and redirect
    $_SESSION['flash'] = array('key' => 'success', 'msg' => 'Movie Updated!');
    return $res->withRedirect($this->container->router->pathFor('movieByID', array('id' => $args['id'])));
  }
}