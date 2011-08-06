<?php 
session_start(); 
if (!isset($_SESSION['OUR_OWN'])) 
  {
    session_regenerate_id();
    $_SESSION['OUR_OWN'] = TRUE;
  } 
?>