<?php
// Main class that interacts with the sql database, stores an instance of an active PDO connection

namespace App\Core\Database;

use PDO;

class QueryDB
{
  // Private instance of PDO
  private $pdo;

  // Constructor to store PDO instance
  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

  // Main method used to query the database by other methods in this class 
  private function query($sql, $params = null)
  {
    // set a return var
    $result = null;

    try {

      $stmt = $this->pdo->prepare($sql);
      $stmt->execute($params);

      if (strpos($sql, "SELECT") !== false || strpos($sql, "SHOW") !== false) {
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      }

    // catch and die on error with msg
    } catch (PDOException $e) {
      die($e->getMessage());
    }
    // return the result
    return $result;
  }

  // Method to select everything from the passed table, either platform or genre
  public function selectAll($table)
  {
    switch ($table) {
      case 'platform':
        $tbl = 'allplatform';
        break;
      case 'genre':
        $tbl = 'allgenre';
        break;
      case 'access':
        $tbl = 'allaccess';
        break;
    }

    $sql = "SELECT * from {$tbl}";

    return $this->query($sql);
  }

  // insert method that handles multiple values, returns the id of the insert once complete
  public function insert($table, $params)
  {

    $sql = sprintf(
      'INSERT INTO %s (%s) VALUES (%s)',
      $table,
      implode(', ', array_keys($params)),
      ':' . implode(', :', array_keys($params))
    );

    $this->query($sql, $params);
    return $this->pdo->lastInsertId();
  }

  // returns the select all from the allstaff view
  public function selectAllStaff()
  {
    // SELECT s.staffID, s.fname, s.sname, s.username, a.name from staff s JOIN access a WHERE s.accessID = a.accessID
    return $this->query("SELECT * FROM allstaff");
  }

  // queries the allmovies view, specifically the title, with the query passed
  public function searchMovies($query)
  {
    // SELECT m.movieID, m.title, m.description, i.mime, i.img FROM movie m JOIN movie_img i ON (m.movieID = i.movieID) ?

    $query = "%{$query}%";
    return $this->query("SELECT * FROM allmovies WHERE title LIKE :title LIMIT 25", array(':title' => $query));
  }

  // method using the movieinventory & moviewithgenre views to find a movie by its ID
  public function findMovieByID($id)
  {
    $platformDetails = $this->query("SELECT * FROM movieinventory WHERE movieID = :movieID", array(':movieID' => $id));

    $movie = $this->query("SELECT * FROM moviewithgenre WHERE movieID = :movieID", array(':movieID' => $id));

    return array('platform' => $platformDetails, 'movie' => $movie);
  }

  // returns true if a movie is found by the title passed
  public function findMovieByTitle($title)
  {
    $result = $this->query("SELECT 1 FROM movie WHERE UPPER(title) = :title", array(':title' => strtoupper($title)));
    if (sizeof($result) > 0) {
      return true;
    }
    return false;
  }

  // transactions to add a new movie and movie_img into the database, rolls back on failure
  public function addNewMovie($imgBlob, $imgType, $movie)
  {
    // start transaction
    $this->pdo->beginTransaction();

    try {

      // get the next auto-increment id's for both tables, movie, movie_img & movie_product
      $nextMovieID = $this->rtnNextID('movie');
      $nextMovieImg = $this->rtnNextID('movie_img');
      
      // disable the foreign key checks
      $stmt = $this->pdo->prepare("SET FOREIGN_KEY_CHECKS=0");
      $stmt->execute();

      $sql = "INSERT INTO movie (title, description, movieImgID, genreID) VALUES (:title, :description, :movieImgID, :genreID)";

      // insert into movie table first
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindParam(':title', $movie['title']);
      $stmt->bindParam(':description', $movie['description']);
      $stmt->bindParam(':movieImgID', $nextMovieImg);
      $stmt->bindParam(':genreID', $movie['genre']);
      $stmt->execute();

      // insert image into table
      $sql = "INSERT INTO movie_img (img, mime, movieID) VALUES (:img, :mime, :movieID)";
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindParam(':img', $imgBlob, PDO::PARAM_LOB);
      $stmt->bindParam(':mime', $imgType);
      $stmt->bindParam(':movieID', $nextMovieID);
      $stmt->execute();

      // enter into the movie_product table per format *DVD & Blu-ray*
      foreach ($movie['platformData'] as $platform => $values) {
        $sql = "INSERT INTO movie_product (movieID, platformID, cost, lateFee, quantity) VALUES (:movieID, (SELECT platformID FROM platform WHERE type = :platform), :cost, :lateFee, :quantity)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':movieID', $nextMovieID);
        $stmt->bindParam(':platform', $platform);
        $stmt->bindParam(':cost', $values['cost']);
        $stmt->bindParam(':lateFee', $values['lateFee']);
        $stmt->bindParam(':quantity', $values['quantity']);
        $stmt->execute();
      }

      // enable the foreign key checks again
      $stmt = $this->pdo->prepare("SET FOREIGN_KEY_CHECKS=1");
      $stmt->execute();

      // commit the addition
      $this->pdo->commit();


    } catch (PDOException $e) {
      // Rollback the transaction due to an error.
      $pdo->rollBack();

      return $e->getMessage();
    }
  }

  // method to update the movie's image/blob
  public function updateMovieImg($imgBlob, $imgType, $movieID)
  {
    $sql = "UPDATE movie_img SET img = :img, mime = :mime WHERE movieID = :movieID";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':img', $imgBlob, PDO::PARAM_LOB);
    $stmt->bindParam(':mime', $imgType);
    $stmt->bindParam(':movieID', $movieID);
    $stmt->execute();
  }

  // method to update movie details
  public function updateMovie($movieDetails)
  {
    $this->query("UPDATE movie SET title = :title, description = :desc, genreID = :genre WHERE movieID = :movieID", array(':title' => $movieDetails['title'], ':desc' => $movieDetails['desc'], ':genre' => $movieDetails['genre'], ':movieID' => $movieDetails['movieID']));
  }

  // method to select multiple movie_products using the moviewithproduct view
  public function getMovieDetails($movieIDs)
  {
    $qs = str_repeat('?,', count($movieIDs) - 1) . '?';
    $sql = "SELECT * FROM moviewithproduct WHERE movieProductID IN ($qs) ORDER BY title ASC, movieProductID ASC";
    return $this->query($sql, $movieIDs);
  }

  // checks to see if the username passed has already been used, returns true/false
  public function usernameTaken($username)
  {

    $result = $this->query("SELECT 1 FROM staff WHERE username = :username LIMIT 1", array(':username' => $username));
    if (sizeof($result) > 0) {
      return true;
    }

    return false;
  }

  // method using the getcreadentials view to see if the username is found which returns the result, otherwise false
  public function verify($username, $password)
  {
    // $result = $this->query("SELECT s.staffID, s.password, s.username, a.name FROM access a INNER JOIN staff s ON s.accessID = a.accessID");

    $result = $this->query("SELECT * FROM getcredentials WHERE  username = :username LIMIT 1", array(':username' => $username));

    if (!sizeof($result) > 0) {
      return false;
      exit();
    }

    $result = $result[0];

    if (password_verify($password, $result['password'])) {
      return $result;
    }
    return false;
  }

  // method to get the next ID for auto-increment tables
  public function rtnNextID($table)
  {
    $result = $this->query("SHOW TABLE STATUS WHERE name = :name", array(':name' => $table));
    $result = $result[0]['Auto_increment'];
    return $result;
  }

  // method to find a customer from the allcustomers view, using the postcode, sname and fname in the $query
  public function findCustomer($query)
  {
    $query = "%{$query}%";

    return $this->query("SELECT * FROM allcustomers WHERE postCode LIKE :postCode OR sname LIKE :sname OR fname LIKE :fname LIMIT 25", array(':postCode' => $query, ':sname' => $query, ':fname' => $query));
  }

  // method using the allcustomers view to find a particular customer by the id
  public function findCustomerByID($id)
  {
    return $this->query("SELECT * FROM allcustomers WHERE customerID = :id", array(':id' => $id));
  }

  // method using the rentalwithdetail view to find all customer orders by the id passed
  public function customerOrders($id)
  {
    return $this->query("SELECT * FROM rentalwithdetail WHERE customerID = :customerID ORDER BY rentalID DESC", array(':customerID' => $id));

    // 
  }

  // method using a transaction to place a customer order. 
  public function makeCustomerOrder($order)
  {
    // start a transaction
    $this->pdo->beginTransaction();

    try {
      // first insert into rental table
      $sql = "INSERT INTO rental (orderDate, returnDate, customerID, staffID) VALUES (:orderDate, :returnDate, :customerID, :staffID)";
      $stmt = $this->pdo->prepare($sql);
      $stmt->execute(array(':orderDate' => $order['orderDate'], ':returnDate' => $order['returnDate'], ':customerID' => $order['customerID'], ':staffID' => $order['staffID']));
      
      // get the last id
      $rentalID = $this->pdo->lastInsertId();

      // values array
      $insert_vals = array();

      // set the id and the movie_product id into the array
      foreach ($_SESSION['movie'] as $movie) {
        $insert_vals[] = $rentalID;
        $insert_vals[] = $movie;
      }

      // bind the question marks depending on the amount of movies ordered
      $bindques = rtrim(str_repeat("(?,?),", count($_SESSION['movie'])), ',');

      // insert into the rental detail table with all the movies
      $sql = "INSERT INTO rental_detail (rentalID, movieProductID) VALUES " . $bindques;

      $stmt = $this->pdo->prepare($sql);
      $stmt->execute($insert_vals);
      
      // commit the addition
      $this->pdo->commit();

    } catch (PDOException $e) {
      // Rollback the transaction due to an error.
      $pdo->rollBack();

      dd($e->getMessage());
    }
  }

  // set the rental_detail value of return to true/1 as movie returned
  public function rtnMovie($id)
  {
    $this->query("UPDATE rental_detail SET returned = TRUE WHERE rentalDetailID = :rentalDetailID", array(':rentalDetailID' => $id));
  }

  // select all from the rentalwithcust view of the movie's late return
  public function custLateRtn($rentalDetID)
  {
    return $this->query("SELECT * FROM rentalwithcust WHERE rentalDetailID = :rentalDetID", array(':rentalDetID' => $rentalDetID));
  }

  // method to see if the customer being registered is already, checking the fname, sname, postcode and email. Case insensitive search, whitespace removed from postcode
  public function checkIfCustomer($customer)
  {
    return $this->query("SELECT * FROM allcustomers WHERE UPPER(fname) = UPPER(:fname) AND UPPER(sname) = UPPER(:sname) AND UPPER(REPLACE(postCode, ' ', '')) = UPPER(REPLACE(:postCode, ' ', '')) OR UPPER(email) = UPPER(:email)", array(':fname' => $customer['fname'], ':sname' => $customer['sname'], ':postCode' => $customer['postCode'], ':email' => $customer['email']));
  }

  // method to update a customer
  public function updateCustomer($id, $params)
  {
    $this->query("UPDATE customer SET fname = :fname, sname = :sname, address = :address, postCode = :postCode, tel = :tel, email = :email WHERE customerID = :customerID", array(':fname' => $params['fname'], ':sname' => $params['sname'], ':address' => $params['address'], ':postCode' => $params['postCode'], ':tel' => $params['tel'], ':email' => $params['email'], ':customerID' => $id));
  }

  // return a select all from the referrers view, the unary customer relationship
  public function allReferrers()
  {
    return $this->query("SELECT * FROM referrers");
    // return $this->query("SELECT c1.customerID, c1.fname, c1.sname, c1.address, c1.postCode, c1.tel, c1.email, CONCAT(c2.fname, ' ', c2.sname) AS referrer FROM customer c1, customer c2 WHERE c1.ref = c2.customerID");
  }

  // return a select all from the staffdetails view, used by admin
  public function staffDetails($id)
  {

    return $this->query("SELECT * FROM staffdetails WHERE staffID = :id LIMIT 1", array(':id' => $id));
  }

  // update the staff's details, used by admin
  public function updateStaff($id, $params)
  {
    $this->query("UPDATE staff SET fname = :fname, sname = :sname, address = :address, accessID = :accessID WHERE staffID = :staffID", array(':fname' => $params['fname'], ':sname' => $params['sname'], ':address' => $params['address'], ':accessID' => $params['accessID'], ':staffID' => $id));
  }

  // method to update a staff's password, done only by an admin
  public function updateStaffPassword($id, $password)
  {
    $this->query("UPDATE staff SET password = :password WHERE staffID = :staffID", array(':password' => $password, 'staffID' => $id));
  }

  // method to delete a member from the staff table, used only by admin. All references are updated to null
  public function deleteStaffMember($id)
  {
    $this->query("DELETE FROM staff WHERE staffID = :id", array(':id' => $id));
    return true;
  }

  // method to return an outer join of all orders along with all staff
  public function orders()
  {
    return $this->query("SELECT * FROM staffwithorders");
  }
}