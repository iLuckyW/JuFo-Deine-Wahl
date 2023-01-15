<!DOCTYPE html>
<?php  
        require_once "modules/postgre/StandardConnection.php";
        require_once "modules/dataClasses/Student.php";
        require_once "modules/dataClasses/School.php";
        require_once "modules/dataClasses/Subject.php";

        $firstYear = "EF";
        $secondYear = "Q1";
        $thirdYear = "Q2";
?>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="assets/styles/style.css">
        <title>Deine Wahl - Übersicht</title>
        <link rel="icon" type="image/x-icon" href="assets/images/icons/logo.png">
    </head>

    <body>
        <header>
            <form method="post">
                <button type="submit" name="subpage" value="main" class="headerLink"><h1><u><b>Deine Wahl</b></u></h1></button>
            </form>
        </header>

        <form method="post">
            <button type="submit" name="subpage" value="logout" class="stdButton" id="logout">Abmelden</button>
        </form>

        <div id="nameBanner">
            <?php 
                $dbConn = new StandardConnection();
                $student = $dbConn->getStudentByToken($_COOKIE["userToken"]); 
                $school = $student->getSchool();
            ?>
            <i><?php echo($school->getName()) ?></i><br><br>
            <b class="subtitle"> Übersicht für <?php echo( $student->getFirstname()." ".$student->getSurname()) ?> </b>
        </div>

        <div class="float-container pushDown">
            <form method="post">
                <div class="schuljahr">
                    <p><b class="subtitle">Wahlen:</b></p>
                    <br>
                    <button type="submit" name="subpage" value="firstSelection" class="stdButton">Zur Erstanwahl</button>
                    <br>
                    <button type="submit" name="subpage" value="table" class="stdButton">Zur Gesamtübersicht</button>
                    <br>
                    <button type="submit" name="subpage" value="staticSelection" class="stdButton">Teilen</button>
                </div>

                <?php 
                $selection = $student->getSelectionTypes(); 
                $subjectnames = $school->getSubjectNames();
                $subjects = $school->getSubjects(); 
                ?>

                <div class="schuljahr">
                    <p><b class="subtitle"> <?php echo($firstYear) ?></b></p>
                    <br>
                    <?php 
                    $slk = array();
                    $mnt = array();
                    $gws = array();
                    $so = array();

                    for($i = 0; $i < count($selection[0]); $i++){
                        if($selection[0][$i] != "-" || $selection[1][$i] != "-"){
                            if($subjects[$i]->hasTag("slk")){
                                array_push($slk, $subjectnames[$i]);
                            }
                            else if($subjects[$i]->hasTag("mnt")){
                                array_push($mnt, $subjectnames[$i]);
                            }
                            else if($subjects[$i]->hasTag("gws")){
                                array_push($gws, $subjectnames[$i]);
                            }
                            else{
                                array_push($so, $subjectnames[$i]);
                            }
                        }
                    }

                    foreach($slk as $sub){
                        echo("<p class=\"slkmain\">".$sub."</p>");
                        echo("<hr>");
                    }

                    foreach($mnt as $sub){
                        echo("<p class=\"mntmain\">".$sub."</p>");
                        echo("<hr>");
                    }

                    foreach($gws as $sub){
                        echo("<p class=\"gwsmain\">".$sub."</p>");
                        echo("<hr>");
                    }

                    foreach($so as $sub){
                        echo("<p class=\"somain\">".$sub."</p>");
                        echo("<hr>");
                    }
                    ?>
                </div>
                <div class="schuljahr">
                    <p><b class="subtitle"> <?php echo($secondYear) ?></b></p>
                    <br>
                    <?php 
                    $slk = array();
                    $mnt = array();
                    $gws = array();
                    $so = array();
                    $lk = array();

                    for($i = 0; $i < count($selection[2]); $i++){
                        if($selection[2][$i] != "-" || $selection[3][$i] != "-"){
                            if($subjects[$i]->hasTag("slk")){
                                array_push($slk, $subjectnames[$i]);
                            }
                            else if($subjects[$i]->hasTag("mnt")){
                                array_push($mnt, $subjectnames[$i]);
                            }
                            else if($subjects[$i]->hasTag("gws")){
                                array_push($gws, $subjectnames[$i]);
                            }
                            else{
                                array_push($so, $subjectnames[$i]);
                            }

                            if($selection[2][$i] == "LK"){
                                array_push($lk, $subjectnames[$i]);
                            }
                        }
                    }

                    foreach($slk as $sub){
                        echo("<p class=\"slkmain\">".$sub);
                        if(in_array($sub, $lk))
                        {
                            echo(" <b>LK</b>");
                        }
                        echo("</p><hr>");
                    }

                    foreach($mnt as $sub){
                        echo("<p class=\"mntmain\">".$sub);
                        if(in_array($sub, $lk))
                        {
                            echo(" <b>LK</b>");
                        }
                        echo("</p><hr>");
                    }

                    foreach($gws as $sub){
                        echo("<p class=\"gwsmain\">".$sub);
                        if(in_array($sub, $lk))
                        {
                            echo(" <b>LK</b>");
                        }
                        echo("</p><hr>");
                    }

                    foreach($so as $sub){
                        echo("<p class=\"somain\">".$sub);
                        if(in_array($sub, $lk))
                        {
                            echo(" <b>LK</b>");
                        }
                        echo("</p><hr>");
                    }
                    ?>
                </div>
                <div class="schuljahr">
                    <p><b class="subtitle"> <?php echo($thirdYear) ?></b></p>
                    <br>
                    <?php 
                    $slk = array();
                    $mnt = array();
                    $gws = array();
                    $so = array();
                    $lk = array();
                    $zk = array();

                    for($i = 0; $i < count($selection[4]); $i++){
                        if($selection[4][$i] != "-" || $selection[5][$i] != "-"){
                            if($subjects[$i]->hasTag("slk")){
                                array_push($slk, $subjectnames[$i]);
                            }
                            else if($subjects[$i]->hasTag("mnt")){
                                array_push($mnt, $subjectnames[$i]);
                            }
                            else if($subjects[$i]->hasTag("gws")){
                                array_push($gws, $subjectnames[$i]);
                            }
                            else{
                                array_push($so, $subjectnames[$i]);
                            }

                            if($selection[4][$i] == "LK"){
                                array_push($lk, $subjectnames[$i]);
                            }
                            else if($selection[4][$i] == "ZK"){
                                array_push($zk, $subjectnames[$i]);
                            }
                        }
                    }

                    foreach($slk as $sub){
                        echo("<p class=\"slkmain\">".$sub);
                        if(in_array($sub, $lk)){
                            echo(" <b>LK</b>");
                        }
                        else if(in_array($sub, $zk)){
                            echo(" <i>ZK</i>");
                        }
                        echo("</p><hr>");
                    }

                    foreach($mnt as $sub){
                        echo("<p class=\"mntmain\">".$sub);
                        if(in_array($sub, $lk)){
                            echo(" <b>LK</b>");
                        }
                        else if(in_array($sub, $zk)){
                            echo(" <i>ZK</i>");
                        }
                        echo("</p><hr>");
                    }

                    foreach($gws as $sub){
                        echo("<p class=\"gwsmain\">".$sub);
                        if(in_array($sub, $lk)){
                            echo(" <b>LK</b>");
                        }
                        else if(in_array($sub, $zk)){
                            echo(" <i>ZK</i>");
                        }
                        echo("</p><hr>");
                    }

                    foreach($so as $sub){
                        echo("<p class=\"somain\">".$sub);
                        if(in_array($sub, $lk)){
                            echo(" <b>LK</b>");
                        }
                        else if(in_array($sub, $zk)){
                            echo(" <i>ZK</i>");
                        }
                        echo("</p><hr>");
                    }
                    ?>
                </div>
            </form>
        </div>

        <!--links to Impressum and additional information-->
        <div class="infoBar">
            <div class="infoStandard"> 
                <a href="impressum.php" id="imprintLink">Impressum</a>
            </div>
        </div>
    </body>
</html>
