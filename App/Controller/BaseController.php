<?php

class BaseController{
   
 protected $pdo;

  function __construct($pdo){
    $this->pdo = $pdo;
  }
   
  function redirect($url){
    header("location: $url");
    exit;
  }


}