<?php
// validator class that checks input values using the Respect Validation library
namespace App\Helpers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as Respect;
use Respect\Validation\Exceptions\NestedValidationException as VException;

class Validator
{
  // errors array
  protected $errors;

  public function checkValues(Request $req, array $rules)
  {
    // loop through the request and assert each rule/validation
    foreach ($rules as $key => $rule) {
      try {
        $rule->setName(ucfirst($key))->assert($req->getParam($key));
      // add errors to the array
      } catch (VException $e) {
        $this->errors[$key] = $e->getMessages();
      }
    }
    // return instance
    return $this;
  }

  // method that returns the errors array
  public function errorsArray()
  {
    return $this->errors;
  }
}