<?php
    require_once "modules/postgre/StandardConnection.php";
    require_once "modules/pdf/StudentPrintout.php";
    require_once "modules/dataClasses/Student.php";
    require_once "modules/dataClasses/School.php";
    require_once "modules/dataClasses/Subject.php";

    if(isset($_POST["subpage"])&&($_POST["subpage"]=="second")) {
        include "templates/createPollSecond.php";
    }
    else if (isset($_POST["subpage"])&&($_POST["subpage"]=="third")) {
        include "templates/createPollThird.php";
    }
    else if (isset($_POST["subpage"])&&($_POST["subpage"]=="guide")) {
        include "templates/createPollGuide.php";
    }
    else if (isset($_POST["subpage"])&&($_POST["subpage"]=="finished")) {
        $faecher = array();
        $schueler = array();
        $conn = new StandardConnection();

        if(isset($_POST["faecher"])) {
            // convert to object
            $faecher = json_decode($_POST["faecher"], true);
            foreach ($faecher as $i=>$subject) {
                $faecher[$i] = new Subject($subject[0], $subject[3]);
            }
        }
        //school's data
        if(isset($_POST["schulname"]) && isset($_POST["schulform"])){
            $schule = new School($_POST["schulname"], $faecher, array(new SelectionType("nicht gewaehlt", "-"), new SelectionType("muendlich", "M"), new SelectionType("schriftlich", "S"), new SelectionType("Leistungskurs", "LK"), new SelectionType("Zusatzkurs", "ZK")));

            // insert into databse
            $conn->insertSchool($schule);
                        
            // update variables
            $schule = $conn->getSchool($conn->findSchool($schule));
            $faecher = $conn->getSchool($conn->findSchool($schule))->getSubjects();
        }
        if(isset($_POST["schueler"]) && isset($_POST["lehrerName"])) {
            // convert to object
            $schueler = json_decode($_POST["schueler"], true);
            foreach ($schueler as $i=>$student) {
                $schueler[$i] = new Student($student[1], $student[0], $student[3], $schule, array_fill(0, 6, array_fill(0, count($faecher), 0)), $student[2]);

                //insert into database
                $conn->insertStudent($schueler[$i], $_POST["lehrerName"], "APO-GOSt");
                
                //language
                foreach ($student[4] as $language) {
                    $conn->insertLanguage($schueler[$i], new Subject($language[1], array()), (float) str_replace("-", ".", $language[2]), (float) str_replace("-", ".", $language[3]));
                }
            }
        }
        //conditions
        if(isset($_POST["bedingungen"])) {
            $bedingungen = json_decode($_POST["bedingungen"], true);
        }

        //show finished screen
        include "templates/createPollFinished.php";
    }
    else if (isset($_POST["subpage"])&&($_POST["subpage"]=="subjects")) {
        include "templates/createPollSubjects.php";
    }
    else if (isset($_POST["subpage"])&&($_POST["subpage"]=="conditions")) {
        include "templates/createPollConditions.php";
    }
    else {
        include "templates/createPollFirst.php";
    }
?>