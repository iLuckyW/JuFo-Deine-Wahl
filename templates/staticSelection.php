<?php
    require_once "modules/postgre/StandardConnection.php";
    require_once "modules/pdf/StudentPrintout.php";
    require_once "modules/dataClasses/Student.php";
    require_once "modules/dataClasses/School.php";
    require_once "modules/dataClasses/Subject.php";

    $dbConn = new StandardConnection();
    //gets student school and subjects
    $student = $dbConn->getStudentByToken($_COOKIE["userToken"]);
    //if this was fired from a teacher account respond with student specified in get method
    if(($student->getFirstname().$student->getSurname())=="KeinSchüler") {
        $student = $dbConn->getAssociatedStudent($_COOKIE["userToken"], $_POST["username"]);
    }
    $school = $student->getSchool();
    $subjects = $school->getSubjects();
    $subjectnames = $school->getSubjectNames();
    $subjectvalues = array(); // saves current selection
    $minSubjects = 1;

    // Cookies for JS
    $subjectTags = $school->getSubjectTags();
    for ($i = 0; $i < count($subjectTags); $i++) {
        $subjectTags[$i] = implode(",", $subjectTags[$i]);
    }
    setcookie("subjects", implode(",", $school->getSubjectNames()), time()+60*60*24);
    setcookie("subjectTags", implode(";", $subjectTags), time()+60*60*24);
    setcookie("selectionTypes", implode(",", $school->getSelectionTypeAbbr()), time()+60*60*24);
    

    ?>
    <!DOCTYPE html>
    <script>
        function setSelection(id, option) {
            const sub = document.getElementById(id);
            if(sub != null) {
                sub.value = option;
            }
        }
    </script>


    <?php
    //
    // group subjects, for individual selection types
    //

    $lkSubjects = array();
    $zkSubjects = array();
    $mandSubjects = array();
    $nonExamSubjects = array();
    $slk = array();
    $mnt = array();
    $gws = array();
    $so = array();
    $vtf = array();

    foreach($subjects as $subject) {
        if($subject->hasTag("lks")) {
            array_push($lkSubjects, $subject->getName());
        }
        if($subject->hasTag("zsk")) {
            array_push($zkSubjects, $subject->getName());
        }
        if($subject->hasTag("pfl")) {
            array_push($mandSubjects, $subject->getName());
        }
        if($subject->getName() == "Sport"){
            array_push($nonExamSubjects, $subject->getName());
        }
        if($subject->hasTag("slk")) {
            array_push($slk, $subject->getName());
        } else if($subject->hasTag("mnt")) {
            array_push($mnt, $subject->getName());
        } else if($subject->hasTag("rel") || $subject->getName() == "Sport"){
            array_push($so, $subject->getName());
        } else if($subject->hasTag("gws")) {
            array_push($gws, $subject->getName());
        } else if($subject->hasTag("vtf")) {
            array_push($vtf, $subject->getName());
        }
    }

    //
    // subejcts from first selection get loaded
    // 

    for($i = 1; $i < 10; $i++) //Naturwissenschaften
    {
        if(isset($_POST["nw".strval($i)])) array_push($subjectvalues, $_POST["nw".strval($i)]);
    }

    for($i = 1; $i < 10; $i++) //Sprachen
    {
         if(isset($_POST["sp".strval($i)])) array_push($subjectvalues, $_POST["sp".strval($i)]);   
    }

    for($i = 1; $i < 10; $i++)//Gesellschaftswissenschaften
    {
        if(isset($_POST["gw".strval($i)])) array_push($subjectvalues, $_POST["gw".strval($i)]);
    }

        for($i = 1; $i < 10; $i++)//Sonstige Fächer
    {
        if(isset($_POST["so".strval($i)])) array_push($subjectvalues, $_POST["so".strval($i)]);
    }

    //delete empty subjects
    $index = 0;
    foreach($subjectvalues as $s) {
        if($s == "empty") array_splice($subjectvalues, $index, 1);
        else $index++;
    }

    if (count($subjectvalues) > $minSubjects){ // if first selection was not skipped

        //delete double listed elements
        for($i = 0; $i < count($subjectvalues); $i++) {
            for($j = $i+1; $j < count($subjectvalues); $j++){ 
                if($subjectvalues[$i] == $subjectvalues[$j]){
                    array_splice($subjectvalues, $j, 1);
                    $j--;
                }
            }
            $i++;
        }
        $loadfromdatabase = false;
    } 
    else{ //if first selection was skipped
        // load subjects from database
        $loadfromdatabase = true;

        $selectedsubjects = $student->getSubjects();

        if(!empty($selectedsubjects)){   
            $selectiontypes = $student->getSelectionTypes();//get selected selection types

            //add all subjects that are selected in at least one semester to subjectvalues

            for($i = 0; $i < count($selectiontypes[0]); $i++) {
                if($selectiontypes[0][$i]!="-" || $selectiontypes[1][$i]!="-" || $selectiontypes[2][$i]!="-" || $selectiontypes[3][$i]!="-" || $selectiontypes[4][$i]!="-" || $selectiontypes[5][$i]!="-") {
                    array_push($subjectvalues, $subjectnames[$i]);
                }
            }

            //load selection types from database
            function getSemester($index) {
                if($index == 0) {
                    return "EF1";
                } else if($index == 1) {
                    return "EF2";
                } else if($index == 2) {
                    return "Q11";
                } else if($index == 3) {
                    return "Q12";
                } else if($index == 4) {
                    return "Q21";
                }else if($index == 5) {
                    return "Q22";
                }
                return null;
            }
        }
        
        // load standard selection (all subjects)
        else {
            foreach($subjects as $subject){
                array_push($subjectvalues, $subject->getName());
            }
        }
    }
?>

<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="assets/styles/style.css">
        <title>Deine Wahl - Fächerwahl</title>
        <link rel="icon" type="image/x-icon" href="assets/images/icons/logo.png">
    </head>

    <body onload="loadSelectedSubs()">
        <header>
            <form method="post">
                <button type="submit" name="subpage" value="main" class="headerLink"><h1><u><b>Deine Wahl</b></u></h1></button>
            </form>
        </header>

        <!--Import jquery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="libaries/jquery-3.6.0.js"></script>
        
        <form method="POST" action="selection.php">

            <div class= "spacer">
                <div id="full-size-box">
                <input type="checkbox" id="showAll" name="alleAnzeigen" onchange="toggleVisibility()"><b>Alle Fächer anzeigen</b>

                <table id ="selection" border=1 frame=hsides rules=rows width="100%" align="center" cellpadding="5">

                    <!-- col-elements limit column width in a table-->
                    <col style="width: 20%">

                    <tr class = "subject">
                        <th></th>
                        <th class="leftText">EF/1</th>
                        <th class="leftText">EF/2</th>
                        <th class="leftText">Q1/1</th>
                        <th class="leftText">Q1/2</th>
                        <th class="leftText">Q2/1</th>
                        <th class="leftText">Q2/2</th>
                        <th class="leftText">Abiturfach</th>
                    </tr>


                    <?php
                        //current subject, which was selected
                        $selectedIndex = -1;
                        //print coloumn for every subject
                        foreach ($subjectnames as $subject) {

                            $selectedIndex++;
                            //set class for each subject for style
                            if(in_array($subject, $slk)) {
                                $class = "slk";
                            } else if(in_array($subject, $mnt)) {
                                $class = "mnt";
                            } else if(in_array($subject, $gws)) {
                                $class = "gws";
                            } else if(in_array($subject, $so)){
                                $class = "so";
                            } else if(in_array($subject, $vtf)){
                                $class = "vtf";
                            }

                            
                            //return for $subject a row - insert subject at given points
                            echo(
                                "<tr class=\"".$class."row\" id=\"".$subject."all\">
                                    <th>".$subject."</th>");

                            if(in_array($subject, $subjectvalues)) {
                                echo(   "<th class=\"semester\" id=\"".$subject."\">
                                            <p id=\"".$subject."EF1\">".$selectiontypes[0][$selectedIndex]."</p>
                                        </th>
                                ");
                                echo(   "<th class=\"semester\" id=\"".$subject."\">
                                            <p id=\"".$subject."EF2\">".$selectiontypes[1][$selectedIndex]."</p>
                                        </th>
                                ");
                                echo(   "<th class=\"semester\" id=\"".$subject."\">
                                            <p id=\"".$subject."Q11\">".$selectiontypes[2][$selectedIndex]."</p>
                                        </th>
                                ");
                                echo(   "<th class=\"semester\" id=\"".$subject."\">
                                            <p id=\"".$subject."Q12\">".$selectiontypes[3][$selectedIndex]."</p>
                                        </th>
                                ");
                                echo(   "<th class=\"semester\" id=\"".$subject."\">
                                            <p id=\"".$subject."Q21\">".$selectiontypes[4][$selectedIndex]."</p>
                                        </th>
                                ");
                                echo(   "<th class=\"semester\" id=\"".$subject."\">
                                            <p id=\"".$subject."Q22\">".$selectiontypes[5][$selectedIndex]."</p>
                                        </th>
                                ");
                            }
                            else {
                                echo(   "<th class=\"semester\" id=\"".$subject."\">
                                            <p id=\"".$subject."EF1\">-</p>
                                        </th>
                                        <th class=\"semester\" id=\"".$subject."\">
                                            <p id=\"".$subject."EF2\">-</p>
                                        </th>
                                        <th class=\"semester\" id=\"".$subject."\">
                                            <p id=\"".$subject."Q11\">-</p>
                                        </th>
                                        <th class=\"semester\" id=\"".$subject."\">
                                            <p id=\"".$subject."Q12\">-</p>
                                        </th>
                                        <th class=\"semester\" id=\"".$subject."\">
                                            <p id=\"".$subject."Q21\">-</p>
                                        </th>
                                        <th class=\"semester\" id=\"".$subject."\">
                                            <p id=\"".$subject."Q22\">-</p>
                                        </th>
                                ");
                            }
                            //abi column
                            echo("  <th class=\"semester\" id=\"".$subject."\">
                                        <p id=\"".$subject."ABI\" name=\"".$subject."ABI\"");
                            if(in_array($subject, $nonExamSubjects)) {
                                            echo("hidden=\"hidden\" >");
                            }
                            echo("-");
                            
                            echo("
                                        </p>
                                    </th>
                                </tr>"
                            );
                        }

                        $abisubs = explode(",", substr($dbConn->getAbifaecher($student), 1, -1));
                            $abitypes = ["1. LK", "2. LK", "3. GKS", "4. GKM"];
                            $num = 0;
                            for($i = 0; $i < count($abisubs); $i++) {
                                if($abisubs[$i] != "-1") {
                                    echo("<script>const sub".$num." = document.getElementById(\"".$subjectnames[intval($abisubs[$i])]."ABI\");
                                                  if(sub".$num." != null) {
                                                    sub".$num.".innerHTML = \"".$abitypes[$i]."\";
                                                  }
                                          </script>");
                                    $num++;
                                }
                            }
                    ?>

                    <tr class = "subject">
                        <th class="subject leftText">Wochenstunden</th>

                        <th id="ef1Les" class="leftText">0</th>
                        <th id="ef2Les" class="leftText">0</th>
                        <th id="q11Les" class="leftText">0</th>
                        <th id="q12Les" class="leftText">0</th>
                        <th id="q21Les" class="leftText">0</th>
                        <th id="q22Les" class="leftText">0</th>

                        <th id="qSumLes" class="leftText">Gesamt: 0</th>
                    </tr>

                    </table>
                    
                    <div class = "line">
                        <img src="assets/images/benno/benno06.png" alt="Benno der Berater" width="10%"> 
                        <table id = "comment">
                        </table>
                    </div>
                </div>
            </div>
        </form>

        <!--links to Impressum and additional information-->
        <div class="infoBar">
            <div class="footer-container">
                <div class="footer-segment">
                </div>

                <div class="footer-segment">
                    
                </div>

                <div class="footer-segment">
                    <div class="progress-standard"> 
                        <a href="impressum.php" id="imprintLink">Impressum</a>
                    </div>
                </div>

                <div class="footer-segment">
                </div>

                <div class="footer-segment">
                </div>
            </div>
        </div>
    </body>
</html>

<!-- JS Methods -->

<script>
    function loadSelectedSubs() {
        var allSubs = <?php echo '["' . implode('", "', $subjectnames) . '"]' ?>;
        var selectedSubs = <?php echo '["' . implode('", "', $subjectvalues) . '"]'?>;

        let subjectTags = getCookie("subjectTags").split(";");
        for(i = 0; i < allSubs.length; i++) {
            if(!selectedSubs.includes(allSubs[i])) {
                document.getElementById(allSubs[i] + "EF1").innerHTML = "-";
                document.getElementById(allSubs[i] + "EF2").innerHTML = "-";
                document.getElementById(allSubs[i] + "Q11").innerHTML = "-";
                document.getElementById(allSubs[i] + "Q12").innerHTML = "-";
                document.getElementById(allSubs[i] + "Q21").innerHTML = "-";
                document.getElementById(allSubs[i] + "Q22").innerHTML = "-";

                document.getElementById(allSubs[i] + "all").style.display = "none";
            }
        }

        //run methods to update sum and warnings
        selectionToCookies();
        checkSelection();
        calculateLessons();
    }

    function toggleVisibility() {
        $(document).ready(function() {
            var allSubs = <?php echo '["' . implode('", "', $subjectnames) . '"]' ?>;
            if(!document.getElementById("showAll").checked) {
                var selectedSubs = [];
                //push all selected subjects
                for(i = 0; i < 6; i++) {
                    for(j = 0; j < allSubs.length; j++) {
                        var selectedValue = document.getElementById(allSubs[j] + getSemester(i)).innerHTML;
                        if(selectedValue != "-" && !selectedSubs.includes(allSubs[j])) {
                            selectedSubs.push(allSubs[j]);
                        }
                    }
                }
                //hide all not selected subjects
                for(i = 0; i < allSubs.length; i++) {
                    if(!selectedSubs.includes(allSubs[i])) {
                        document.getElementById(allSubs[i] + "all").style.display = "none";
                    }
                }
            }
            else {
                for(i = 0; i < allSubs.length; i++) {
                    document.getElementById(allSubs[i] + "all").style.display = "";
                }
            }
        })
    }

    function getSemester(index) {
        if(index == 0) {
            return "EF1";
        } else if(index == 1) {
            return "EF2";
        } else if(index == 2) {
            return "Q11";
        } else if(index == 3) {
            return "Q12";
        } else if(index == 4) {
            return "Q21";
        }else if(index == 5) {
            return "Q22";
        } return null;
    }

    //creates/removes row with text "comment" depending if visible is set true
    function setVisibility(comment, visible) {
        if (visible  && document.getElementById(comment) == null) {
            let table = document.getElementById("comment");

            let row = table.insertRow(0);
            row.id = comment;

            let cell = row.insertCell(0);
            cell.innerHTML = comment;
        } else if (!visible  && document.getElementById(comment) != null) {
            let row = document.getElementById(comment);
            document.getElementById("comment").deleteRow(row.rowIndex);
        }
    }

    class Highlighter{
        static highlighted;
    }

    //highlights a row by making its border blink red
    function highlightElement(element, active) {
        console.log(active);
        if(active) {
            element.style.border = "thick solid #FF0000";
        }
        else {
            element.style.border = "none";
        }
    }

    function getCookie(key) {
        let cookies = document.cookie.split("; ");
        let value = null;

        // find key in cookies
        for (const cookie of cookies) {
            if (cookie.substr(0,cookie.indexOf("=")) == key) value = decodeURIComponent(cookie.substr(cookie.indexOf("=") + 1));
        }

        return value;
    }

    function selectionToCookies() {
        let subjects = getCookie("subjects").split(",");
        let selectionTypes = getCookie("selectionTypes").split(",");

        // create empty selection https://stackoverflow.com/a/52268285
        let selection = new Array(6).fill(0).map(() => new Array(subjects.length).fill(0));

        // read selection from table in web page
        var table = document.getElementById("selection");

        // skip first and last row (column title and weekly hours)
        for (let i = 1; i < table.rows.length - 1; i++) {
            // find index of subject
            let indexSubject = 0;
            while (indexSubject < subjects.length && subjects[indexSubject] != table.rows[i].cells[0].innerHTML) {indexSubject++}
            if (subjects[indexSubject] != table.rows[i].cells[0].innerHTML) continue;

            // skip first (row title and last abitur)
            for (let j = 1; j < 7; j++) {
                select = table.rows[i].cells[j].children[0];

                // find index of selectionType
                let indexSelType = 0;
                while (indexSelType < selectionTypes.length && selectionTypes[indexSelType] != select.innerHTML) {indexSelType++}
                if (selectionTypes[indexSelType] != select.innerHTML) continue;

                selection[j - 1][indexSubject] = indexSelType;
            }
        }

        // save selection in cookies
        document.cookie = "selection=" + encodeURIComponent(selection.join(";"));
    }

    function checkSelection() {
        // load necessary data
        let selectionTypes = getCookie("selectionTypes").split(",");
        let subjects = getCookie("subjects").split(",");
        let subjectTags = getCookie("subjectTags").split(";");
        let selection = getCookie("selection").split(";");

        for (let i = 0; i < selection.length; i++) {
            selection[i] = selection[i].split(",");
        }

        // a subject can only be selected if it was selected prior (excluding zk and vtf)
        for (let i = 0; i < subjects.length; i++) {
            if (subjectTags[i].includes("ltk") || subjectTags[i].includes("pjk")) continue;
            let j = 0;
            while (j < selection.length && selection[j][i] != 0) j++;
            while (j < selection.length && (selection[j][i] == 0 || selection[j][i] == 4)) j++;
            setVisibility(subjects[i] + " wurde nicht kontinuierlich belegt.", (j < selection.length));
            //add blinking to text row
            if(j < selection.length) {
                let comment = document.getElementById(subjects[i] + " wurde nicht kontinuierlich belegt.");
                comment.addEventListener('mouseover', (event) => {
                    //find correct target
                    let correctTarget = event.target.parentElement;
                    Highlighter.highlighted = correctTarget;
                    let rowName = correctTarget.id.substring(0, correctTarget.id.length - 35)+"all";
                    if(rowName.length > 3) {
                        let row = document.getElementById(rowName);
                        if(row != null) {
                            highlightElement(row, true);
                        }
                    }
                });
                comment.addEventListener('mouseleave', (event) => {
                    //find correct target
                    let correctTarget = Highlighter.highlighted;
                    let rowName = correctTarget.id.substring(0, correctTarget.id.length - 35)+"all";
                    if(rowName.length > 3) {
                        let row = document.getElementById(rowName);
                        if(row != null) {
                            highlightElement(row, false);
                        }
                    }
                });
            }
        }
        
        // EF to Q2 checks
        let pbc = 0; // a science (phyisic, biology or chemistry) subject was selected from ef to q2
        let mnt = 0;
        let gws = 0;
        let fsp = 0;
        for (let i = 0; i < subjects.length; i++) {
            if (subjectTags[i].includes("pfl")) {
                let j = 0;
                while (j < selection.length && selection[j][i] != 0) j++;
                setVisibility(subjects[i] + " ist ein Pflichtfach und muss von der EF - Q2 belegt sein.", (j < selection.length));
            } else if (subjects[i] == "Biologie" || subjects[i] == "Physik" || subjects[i] == "Chemie" || subjects[i] == "Biologie bilingual") {
                let j = 0;
                while (j < selection.length && selection[j][i] != 0) j++;
                if (j == selection.length) pbc++;
            }
            if (subjectTags[i].includes("gws")) {
                let j = 0;
                while (j < selection.length && selection[j][i] != 0) j++;
                if (j == selection.length) gws++;
            }
            if (subjectTags[i].includes("mnt")) {
                let j = 0;
                while (j < selection.length && selection[j][i] != 0) j++;
                if (j == selection.length) mnt++;
            }
            if (subjectTags[i].includes("fsp")) {
                let j = 0;
                while (j < selection.length && selection[j][i] != 0) j++;
                if (j == selection.length) fsp++;
            }
        }
        setVisibility("Eines der Fächer Biologie, Physik oder Chemie muss durchgängig belegt sein.", (pbc == 0));
        setVisibility("Es müssen entweder zwei Fremdsprachen oder zwei naturwissenschaftlich-technische Fächer belegt sein.", (gws < 2) && ((pbc + mnt) < 2));
        setVisibility("Eine Fremdsprache muss durchgängig belegt sein.", (fsp == 0));
        setVisibility("Ein gesellschaftswissenschaftlichen Aufgabenfeld muss durchgängig belegt sein.", (gws == 0));

        // EF - Checks
        let sub = 0;
        gws = 0;
        let rel = 0;
        let kmu = 0;
        for (let i = 0; i < subjects.length; i++) {
            if (selection[0][i] != 0 && selection[1][i] != 0) sub++;
            if (subjectTags[i].includes("gws") && !subjectTags[i].includes("rel") && selection[0][i] != 0 && selection[1][i] != 0) gws++;
            if (subjectTags[i].includes("rel") && selection[0][i] != 0 && selection[1][i] != 0) rel++;
            if ((subjects[i] == "Kunst" || subjects[i] == "Musik") && selection[0][i] != 0 && selection[1][i] != 0) kmu++;
        }
        setVisibility("Es müssen mindestens elf Fächer in der EF belegt sein.", (sub < 11));
        setVisibility("Mindestens ein Fach aus dem gesellschaftswissenschaftliches Aufgabenfeld muss in der EF belegt sein.", (gws == 0));
        setVisibility("Religion oder Philosophie muss in der EF belegt sein.", (rel == 0));
        setVisibility("Musik oder Kunst muss in der EF belegt sein.", (kmu == 0));

        // Q - Checks
        let lks = 0;
        rel = 0;
        kmu = 0;
        for (let i = 0; i < subjects.length; i++) {
            if (selection[2][i] == 3) lks++;
            if (subjectTags[i].includes("rel") && selection[2][i] != 0 && selection[3][i] != 0) rel++;
            if ((subjects[i] == "Kunst" || subjects[i] == "Musik") && selection[2][i] != 0 && selection[3][i] != 0) kmu++;
            if (subjects[i] == "Geschichte") setVisibility("Geschichte muss in der Q1.1 und Q1.2 belegt sein, oder als Zusatzkurs in der Q2.1 und Q2.2.", ((selection[2][i] == 0 || selection[3][i] == 0) && (selection[4][i] != 4 || selection[5][i] != 4)));
            if (subjects[i] == "Sozialwissenschaften") setVisibility("Sozialwissenschaft muss in der Q1.1 und Q1.2 belegt sein, oder als Zusatzkurs in der Q2.1 und Q2.2.", ((selection[2][i] == 0 || selection[3][i] == 0) && (selection[4][i] != 4 || selection[5][i] != 4)));
        }
        setVisibility("Es müssen genau zwei Leistungskurse in der Q-Phase belegt sein.", (lks != 2));
        setVisibility("Religion oder Philosophie muss in der Q1.1 und Q1.2 belegt sein.", (rel == 0));
        setVisibility("Kunst oder Musik muss in der Q1.1 und Q1.2 belegt sein.", (kmu == 0));
    }

    function calculateLessons() {
        // load necessary data
        let selectionTypes = getCookie("selectionTypes").split(",");
        let subjects = getCookie("subjects").split(",");
        let subjectTags = getCookie("subjectTags").split(";");
        let selection = getCookie("selection").split(";");

        for (let i = 0; i < selection.length; i++) {
            selection[i] = selection[i].split(",");
        }

        //get lk index
        const lkIndex = selectionTypes.indexOf("LK");
        const noneIndex = selectionTypes.indexOf("-");

        const lessons = new Array();
        for(let i = 0; i < selection.length; i++) {
            let semesterLessons = 0;
            selection[i].forEach((row, index) => {
                if(parseInt(row) === noneIndex) {
                    semesterLessons = semesterLessons;
                }
                else {
                    //check if lk
                    if(parseInt(row) === lkIndex) {
                        semesterLessons = semesterLessons + 5;
                    }
                    else {
                        //check if new foreign language
                        let tags = subjectTags[index].split(",");
                        if(tags.includes("nfs")) {
                            semesterLessons = semesterLessons + 4;
                        }
                        else {
                            semesterLessons = semesterLessons + 3;
                        }
                    }
                }
            });
            //add sum to array
            lessons.push(semesterLessons);
        }

        //write into fields
        document.getElementById('ef1Les').innerHTML = lessons[0];
        document.getElementById('ef2Les').innerHTML = lessons[1];
        document.getElementById('q11Les').innerHTML = lessons[2];
        document.getElementById('q12Les').innerHTML = lessons[3];
        document.getElementById('q21Les').innerHTML = lessons[4];
        document.getElementById('q22Les').innerHTML = lessons[5];
        document.getElementById('qSumLes').innerHTML = "Gesamt: "+((lessons[0]+lessons[1]+lessons[2]+lessons[3]+lessons[4]+lessons[5])/2);
    }

    // add change listener
    $(document).ready(function(){
        $('.semesterauswahl').change(function() {
            selectionToCookies();
            checkSelection();
            calculateLessons();
        })
    });
</script>