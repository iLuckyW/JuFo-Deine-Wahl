<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="assets/styles/style.css">
        <title>Deine Wahl - Admin Panel</title>
        <link rel="icon" type="image/x-icon" href="assets/images/icons/logo.png">
    </head>

    <?php
        //load classes
        require_once "modules/postgre/StandardConnection.php";
        require_once "modules/pdf/StudentPrintout.php";
        //handle Button functions
        if(isset($_POST["function"])) {
            switch($_POST["function"]) {
                case("createSchoolTable"):
                    $dbConn = new StandardConnection();
                    $dbConn->createSchoolTable($_POST["crScTName"]);
                    break;
                case("createStudentTable"):
                    $dbConn = new StandardConnection();
                    $dbConn->createStudentTable($_POST["crTName"], $_POST["crSRef"]);
                    break;
                case("deleteTable"):
                    $dbConn = new StandardConnection();
                    $dbConn->deleteTable($_POST["dlTName"]);
                    break;
                case("printPdf"):
                    define('FPDF_FONTPATH', 'libaries/font/');
                    $pdf = new StudentPrintout();
                    $pdf->configureStandard();
                    $pdf->printHead("Annette-von-Droste-Hülshoff Gymnasium Städt.Gymnasium", $_POST["prPdfName"], $_POST["prPdfFirstName"], "Q1.1", "9C");
                    //create test selection
                    $courses = array("Deutsch","Englisch","Chinesisch, Beginn in 10","Musik","Geschichte","Geographie","Sozialwissenschaft","Philosophie","Mathematik","Physik","Chemie","Sport");
                    $selection = array(array("S","S","LK","LK","LK","LK"), array("S","S","S","S","S","S"), array("S","S","","","",""), array("M","M","M","M","M","M"), array("M","M","M","M","M","M"), array("S","S","S","S","S","S"), array("","","","","ZK","ZK"), array("M","M","M","M","",""), array("S","S","LK","LK","LK","LK"), array("S","S","S","S","S","S"), array("S","S","S","S","S","S"), array("M","M","M","M","M","M"));
                    $pdf->printTable($courses, $selection);
                    $pdf->printNotes();
                    $pdf->printSignatureLines();
                    $pdf->printFoot();
                    $pdf->showPdf();
                    break;
                case("insertStudent"):
                    $dbConn = new StandardConnection();
                    $selection = array(array("S","S","S","S","S","S"), array("S","S","S","S","S","S"), array("S","S","S","S","S","S"), array("M","M","M","M","M","M"), array("M","M","M","M","M","M"), array("S","S","S","S","S","S"), array(" "," "," "," ","Z","Z"), array("M","M","M","M","M","M"), array("S","S","S","S","S","S"), array("S","S","S","S","S","S"), array("S","S","S","S","S","S"), array("M","M","M","M","M","M"));
                    $dbConn->insertStudent($_POST["inStTName"], $_POST["inStFirstName"], $_POST["inStName"], $_POST["inStStufe"], 1, $selection);
                    break;
                case("insertSchool"):
                    $dbConn = new StandardConnection();
                    //
                    $dbConn->testInsertSchool($_POST["inScTName"], $_POST["inScName"]);
                    break;
                case("setupDatabase"):
                    $dbConn = new StandardConnection();
                    $dbConn->setupDatabase();
                default:
            }
        }
    ?>

    <body>
        
        <!-- Background image is set here-->
        <div class="bg-image"></div>


        <!-- Header and title of website-->
        <header>
            <h1><u><b>Deine Wahl  - Admin Panel</b></u></h1>
        </header>
        <form method="post">
            <input type="text" id="crScTName" name="crScTName" placeholder="Name der Tabelle..."></input>
            <button type="submit" name="function" value="createSchoolTable">Schultabelle ersterllen!</button>
            <br>
            <input type="text" id="crTName" name="crTName" placeholder="Name der Tabelle..."></input>
            <input type="text" id="crSRef" name="crSRef" placeholder="zugehörige Schultabelle"></input>
            <button type="submit" name="function" value="createStudentTable">Schülertabelle ersterllen!</button>
            <br>
            <input type="text" id="dlTName" name="dlTName" placeholder="Name der Tabelle..."></input>
            <button type="submit" name="function" value="deleteTable">Tabelle löschen!</button>
            <br>
            <input type="text" id="prPdfFirstName" name="prPdfFirstName" placeholder="Vorname des Schuelers..."></input>
            <input type="text" id="prPdfName" name="prPdfName" placeholder="Name des Schuelers..."></input>
            <button type="submit" name="function" value="printPdf">Bogen erstellen!</button>
            <br>
            <input type="text" id="inStTName" name="inStTName" placeholder="Name der Tabelle..."></input>
            <input type="text" id="inStFirst" name="inStFirstName" placeholder="Vorname des Schuelers..."></input>
            <input type="text" id="inStName" name="inStName" placeholder="Name des Schuelers..."></input>
            <input type="text" id="inStStufe" name="inStStufe" placeholder="Stufe des Schuelers..."></input>
            <button type="submit" name="function" value="insertStudent">Schüler einfügen!</button>
            <br>
            <input type="text" id="inScTName" name="inScTName" placeholder="Name der Tabelle..."></input>
            <input type="text" id="inScName" name="inScName" placeholder="Name des Schule..."></input>
            <button type="submit" name="function" value="insertSchool">Schule einfügen!</button>
            <br>
            <button type="submit" name="function" value="setupDatabase">Datenbank aufsetzen!</button>
        </form>
    </body>
</html>