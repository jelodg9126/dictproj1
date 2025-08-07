<?php

class BaseController
{

  protected $pdo;

  function __construct($pdo)
  {
    $this->pdo = $pdo;
  }

  function redirect($url)
  {
    header("location: $url");
    exit;
  }

  public function getUserLocationName()
  {
    if (!isset($_SESSION['userID'])) return 'DICT';

    $authModel = new AuthModel($this->pdo);
    $fullLocation = $authModel->getUserLocation($_SESSION['userID']);

    if (!$fullLocation) return 'DICT';

    // Get last word, e.g. "Pampanga"
    $parts = explode(' ', trim($fullLocation));
    return end($parts);
  }
}
