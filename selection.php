<?php
    require_once "modules/postgre/StandardConnection.php";
    require_once "modules/pdf/StudentPrintout.php";
    require_once "modules/dataClasses/Student.php";
    require_once "modules/dataClasses/School.php";
    require_once "modules/dataClasses/Subject.php";
    //check Password
    $dbConn = new StandardConnection();
    if(isset($_COOKIE["userToken"]) && $dbConn->checkToken($_COOKIE["userToken"])) {
      //check if teacher
      if($dbConn->isTeacher($_COOKIE["userToken"])) {
        if(isset($_POST["subpage"])&&($_POST["subpage"]=="staticSelection")) {
          include "templates/staticSelection.php";
        }
        else if(isset($_POST["subpage"]) && $_POST["subpage"] == "logout"){
          unset($_COOKIE["userToken"]);
          setcookie("userToken", "", 0);
          include "templates/login.php";
        }
        else if(isset($_POST["subpage"])&&($_POST["subpage"]=="print")&&isset($_POST["username"])) {
          $pdf = new StudentPrintout();
          $dbConn = new StandardConnection();
          $student = $dbConn->getAssociatedStudent($_COOKIE["userToken"], $_POST["username"]);
          $examReg = $dbConn->getExamReg($student);
          $supportTeacher = $dbConn->getSupportTeacher($student);
          $abi = explode(",", substr($dbConn->getAbifaecher($student), 1, -1));
          $pdf->fullPrintOut($student, $examReg, $supportTeacher, $abi);
        }
        else {
          include "templates/teacherMain.php";
        }
      }
      else if(isset($_POST["subpage"])&&($_POST["subpage"]=="table")) {
        include "templates/table.php";
      }
      else if(isset($_POST["subpage"])&&($_POST["subpage"]=="firstSelection")) {
        include "templates/firstSelection.php";
      }
      else if(isset($_POST["subpage"])&&($_POST["subpage"]=="staticSelection")) {
        include "templates/staticSelection.php";
      }
      else if(isset($_POST["subpage"])&&($_POST["subpage"]=="print")) {
        $pdf = new StudentPrintout();
        $dbConn = new StandardConnection();
        $student = $dbConn->getStudentByToken($_COOKIE["userToken"]);
        $examReg = $dbConn->getExamReg($student);
        $supportTeacher = $dbConn->getSupportTeacher($student);
        $abi = explode(",", substr($dbConn->getAbifaecher($student), 1, -1));
        $pdf->fullPrintOut($student, $examReg, $supportTeacher, $abi);
      }
      //save values to database
      else if(isset($_POST["subpage"])&&($_POST["subpage"]=="save")) {
        if(!isset($_COOKIE["selection"])) {
          echo("No subjects to store");
          return;
        }

        // store cookie in two dim array
        $selection = array();
        $cookie = explode(";", urldecode($_COOKIE["selection"]));
        foreach ($cookie as $halfyear) {
          array_push($selection, explode(",", $halfyear));
        }

        $abi = explode(";", urldecode($_COOKIE["abi"]));

        //update databes entry
        $userName = $dbConn->getUsernameByToken($_COOKIE["userToken"]);
        $dbConn->updateStudentSelection($userName, $selection, $abi);

        //load site
        include "templates/table.php";
      }
      else if(isset($_POST["subpage"]) && $_POST["subpage"] == "logout") {
        unset($_COOKIE["userToken"]);
        setcookie("userToken", "", 0);
        include "templates/login.php";
      }
      else {
        include "templates/main.php";
      }
    }
    else {
      //if wrong information was sent, send back to login
      include "templates/login.php";
      echo '<script>alert("Das eingegbene Passwort ist falsch.")</script>';
    }

    /*if(isset($_POST["subpage"])) {
        include "templates/table.php";
    }
    else {
        include "templates/main.php";
    }*/
?>
