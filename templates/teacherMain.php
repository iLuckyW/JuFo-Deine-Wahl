<!DOCTYPE html>
<?php  
        require_once "modules/postgre/StandardConnection.php";
        require_once "modules/dataClasses/Student.php";
        require_once "modules/dataClasses/School.php";
        require_once "modules/dataClasses/Subject.php";

        $dbConn = new StandardConnection();
        $studentsAndUsernames = $dbConn->getAssociatedStudents($_COOKIE["userToken"]);
        //split into seperate arrays
        $students = $studentsAndUsernames['students'];
        $usernames = $studentsAndUsernames['usernames'];
?>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="assets/styles/style.css">
        <title>Deine Wahl - Übersicht</title>
        <link rel="icon" type="image/x-icon" href="assets/images/icons/logo.png">
    </head>

    <body onload="insertValues()">
        <header>
            <h1><u><b>Deine Wahl</b></u></h1>
        </header>

        <form method="post">
            <button type="submit" name="subpage" value="logout" class="stdButton" id="logoutTeacher">Abmelden</button>
        </form>

        <div id="nameBanner">
            <b class="subtitle"> Übersicht über Schüler der <?php $dbConn = new StandardConnection(); $teacher = $dbConn->getTeacherByToken($_COOKIE["userToken"]); echo( $teacher->getGrade()) ?> </b>
        </div>

        <div class="mainSite">
            <form method="post" name="mainForm">
                <!--Infokasten-->
                <div class="pupilSelectionTable">
                    <table class="overviewTable" id="pupilTable">
                        <!--Headline-->
                        <tr class="overviewRow">
                            <th class="overviewValue">Name</th>
                            <th class="overviewValue">Vorname</th>
                            <th class="overviewValue">Klasse</th>
                            <th class="overviewValue">Wahl</th>
                            <th class="overviewValue">PDF</th>
                        </tr>
                        <!--values-->
                        <?php
                            $index = 0;
                            foreach ($students as $student) {
                                echo(
                                    "<tr class=\"overviewRow\">
                                        <td class=\"overviewValue\">".$student->getSurname()."</th>
                                        <td class=\"overviewValue\">".$student->getFirstname()."</th>
                                        <td class=\"overviewValue\">".$student->getGrade()."</th>
                                        <td class=\"overviewValue\"><button type=\"button\" onClick=\"openStaticSelection('".$usernames[$index]."')\" class=\"stdButton\">Wahl</button></th>
                                        <td class=\"overviewValue\"><button type=\"button\" onClick=\"openPrintout('".$usernames[$index]."')\" class=\"stdButton\">Ausdruck</button></th>
                                    </tr>"
                                );
                                $index++;
                            }
                        ?>
                    </table>
                </div>
                <!--hidden fields for data transmission-->
                <input type=hidden id=subpage name=subpage></input>
                <input type=hidden id=username name=username></input>
            </form>
        </div>

        <!--links to Impressum and additional information-->
        <div class="infoBar">
            <div class="infoStandard"> 
                <a href="impressum.php" id="imprintLink">Impressum</a>
            </div>
        </div>
    </body>

    <script>
        function insertValues(){
    
        }

        function openStaticSelection(username) {
            document.getElementById('subpage').value = "staticSelection";
            document.getElementById('username').value = username;
            document.mainForm.submit();
        }

        function openPrintout(username) {
            document.getElementById('subpage').value = "print";
            document.getElementById('username').value = username;
            document.mainForm.submit();
        }
    </script>
</html>