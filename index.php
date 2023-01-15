<?php
    //load classes
    ini_set('max_execution_time', '300');
    require_once "modules/postgre/StandardConnection.php";
    $dbConn = new StandardConnection();

    //handle incoming form data
    //check if data was received
    if(isset($_POST["uName"])) {
      //check login information
      if($dbConn->checkPassword($_POST["sNum"], $_POST["uName"], $_POST["pWord"]))  {
        //if correct information was send save login and send to next selection
        setcookie("userToken", $dbConn->generateToken($dbConn->getStudent($_POST["uName"], $_POST["sNum"])), time()+(24*60*60));
        //send to selection
        ob_start();
        header('Location: selection.php');
        ob_end_flush();
        die();
      }
      //check if not student but teacher
      else if($dbConn->checkTeacherPassword($_POST["sNum"], $_POST["uName"], $_POST["pWord"])){
        //if correct information was send save login and send to next selection
        setcookie("userToken", $dbConn->generateTeacherToken($dbConn->getTeacher($_POST["uName"], $_POST["sNum"])), time()+(24*60*60));
        //send to selection
        ob_start();
        header('Location: selection.php');
        ob_end_flush();
        die();
      }
      else {
        //else redirect to login with Warning
        include "templates/login.php";
        echo '<script>alert("Die eingegebenen Anmeldedaten sind falsch.")</script>';
      }
    }
    // else token still in register
    else if (isset($_COOKIE["userToken"]) && $dbConn->checkToken($_COOKIE["userToken"])) {
      //send to selection
      ob_start();
      header('Location: selection.php');
      ob_end_flush();
      die();
    }
    //else redirect to login
    else {
      include "templates/login.php";
    }
?>
