<?php
    require_once "modules/postgre/StandardConnection.php";
    require_once "modules/pdf/StudentPrintout.php";
    require_once "modules/dataClasses/Student.php";
    require_once "modules/dataClasses/School.php";
    require_once "modules/dataClasses/Subject.php";

    $dbConn = new StandardConnection();
    //gets student school and subjects
    $student = $dbConn->getStudentByToken($_COOKIE["userToken"]);
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
        if($subject->hasTag("mdl")){
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
            /*
            //create two dimensional array of selection types of selected subjects
            $selectedtypes = array(array(), array(), array(), array(), array(), array());

            //only add selection types of selected subjects
            for($sub = 0; $sub < count($selectiontypes[0]); $sub++) {
                $flag = false;
                for($sem = 0; $sem < count($selectiontypes); $sem++) {
                    if($selectiontypes[$sem][$sub] != "-") {
                        $flag = true;
                    }
                }
                if($flag) {
                    for($n = 0; $n < 6; $n++) {
                        array_push($selectedtypes[$n], $selectiontypes[$n][$sub]);
                    }
                }
            }*/
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

    <body class="warmBackground" onload="loadSelectedSubs()">
        <header>
            <form method="post">
                <button type="submit" name="subpage" value="main" class="headerLink"><h1><u><b>Deine Wahl</b></u></h1></button>
            </form>
        </header>

        <!--Import jquery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="libaries/jquery-3.6.0.js"></script>
        <!-- set all necessary cookies -->
        <script>
            $(document).ready(function() {
                selectionToCookies();
            })
        </script>
        
        <form method="POST" action="selection.php">

            <div class= "spacer">
                <div id="full-size-box">
                <input type="checkbox" id="selectAll" name="Hochschreiben"><b>Auf folgende Semester übernehmen</b><br>
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
                        //print colomn for every subject
                        foreach ($subjectnames as $subject) {
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
                                    <th>".$subject."</th>
                                    <th class=\"semester\" id=\"".$subject."\">
                                        <select id=\"".$subject."EF1\" class=\"semesterauswahl\" name=\"".$subject."EF1\" onchange=\"selectAllSemesters(this.id)\">
                                            <!--optgroup-tags group different dropdown options-->
                                            <optgroup label=\"Grundkurs\">");
                                            if(!in_array($subject, $nonExamSubjects))
                                            {
                                                echo("<option value=\"s\">S</option>");
                                            }   
                            echo(          "<option value=\"m\">M</option>
                                            </optgroup>");
                                            if(!in_array($subject, $mandSubjects))
                                            {
                                                echo ("<option value=\"-\">-</option>");
                                            }
                            echo(
                                        "</select>
                                    </th>

                                    <th class=\"semester\" id=\"".$subject."\">
                                        <select id=\"".$subject."EF2\" class=\"semesterauswahl\" name=\"".$subject."EF2\" onchange=\"selectAllSemesters(this.id)\">
                                            <!--optgroup-tags group different dropdown options-->
                                            <optgroup label=\"Grundkurs\">");
                                            if(!in_array($subject, $nonExamSubjects))
                                            {
                                                echo("<option value=\"s\">S</option>");
                                            }   
                            echo(          "<option value=\"m\">M</option>
                                            </optgroup>");
                                            if(!in_array($subject, $mandSubjects))
                                            {
                                                echo ("<option value=\"-\">-</option>");
                                            }
                            echo(
                                        "</select>
                                    </th>

                                    <th class=\"semester\" id=\"".$subject."\">
                                        <select id=\"".$subject."Q11\" class=\"semesterauswahl\" name=\"".$subject."Q11\" onchange=\"selectAllSemesters(this.id)\">
                                            <!--optgroup-tags group different dropdown options-->
                                            <optgroup label=\"Grundkurs\">");
                                            if(!in_array($subject, $nonExamSubjects))
                                            {
                                                echo("<option value=\"s\">S</option>");
                                            }   
                            echo(          "<option value=\"m\">M</option>
                                            </optgroup>");
                                            if(in_array($subject, $lkSubjects))
                                            {
                                                echo ("<optgroup label=\"Leistungskurs\">
                                                    <option value=\"lk\">LK</option>
                                                </optgroup>");
                                            }
                                            if(!in_array($subject, $mandSubjects))
                                            {
                                                echo ("<option value=\"-\">-</option>");
                                            }
                            echo(
                                        "</select>
                                    </th>

                                    <th class=\"semester\" id=\"".$subject."\">
                                        <select id=\"".$subject."Q12\" class=\"semesterauswahl\" name=\"".$subject."Q12\" onchange=\"selectAllSemesters(this.id)\">
                                            <!--optgroup-tags group different dropdown options-->
                                            <optgroup label=\"Grundkurs\">");
                                            if(!in_array($subject, $nonExamSubjects))
                                            {
                                                echo("<option value=\"s\">S</option>");
                                            }   
                            echo(          "<option value=\"m\">M</option>
                                            </optgroup>");
                                            if(in_array($subject, $lkSubjects))
                                            {
                                                echo ("<optgroup label=\"Leistungskurs\">
                                                    <option value=\"lk\">LK</option>
                                                </optgroup>");
                                            }
                                            if(!in_array($subject, $mandSubjects))
                                            {
                                                echo ("<option value=\"-\">-</option>");
                                            }
                            echo(
                                        "</select>
                                    </th>

                                    <th class=\"semester\" id=\"".$subject."\">
                                        <select id=\"".$subject."Q21\" class=\"semesterauswahl\" name=\"".$subject."Q21\" onchange=\"selectAllSemesters(this.id)\">
                                            <!--optgroup-tags group different dropdown options-->
                                            <optgroup label=\"Grundkurs\">");
                                            if(!in_array($subject, $nonExamSubjects))
                                            {
                                                echo("<option value=\"s\">S</option>");
                                            }   
                            echo(          "<option value=\"m\">M</option>
                                            </optgroup>");
                                            if(in_array($subject, $lkSubjects))
                                            {
                                                echo ("<optgroup label=\"Leistungskurs\">
                                                    <option value=\"lk\">LK</option>
                                                </optgroup>");
                                            }
                                            if(in_array($subject, $zkSubjects))
                                            {
                                                echo ("<optgroup label=\"Zusatzkurs\">
                                                    <option value=\"zk\">ZK</option>
                                                </optgroup>");
                                            }
                                            if(!in_array($subject, $mandSubjects))
                                            {
                                                echo ("<option value=\"-\">-</option>");
                                            }
                            echo(
                                        "</select>
                                    </th>

                                    <th class=\"semester\" id=\"".$subject."\">
                                        <select id=\"".$subject."Q22\" class=\"semesterauswahl\" name=\"".$subject."Q22\" onchange=\"selectAllSemesters(this.id)\">
                                            <!--optgroup-tags group different dropdown options-->
                                            <optgroup label=\"Grundkurs\">");
                                            if(!in_array($subject, $nonExamSubjects))
                                            {
                                                echo("<option value=\"s\">S</option>");
                                            }   
                            echo(          "<option value=\"m\">M</option>
                                            </optgroup>");
                                            if(in_array($subject, $lkSubjects))
                                            {
                                                echo ("<optgroup label=\"Leistungskurs\">
                                                    <option value=\"lk\">LK</option>
                                                </optgroup>");
                                            }
                                            if(in_array($subject, $zkSubjects))
                                            {
                                                echo ("<optgroup label=\"Zusatzkurs\">
                                                    <option value=\"zk\">ZK</option>
                                                </optgroup>");
                                            }
                                            if(!in_array($subject, $mandSubjects))
                                            {
                                                echo ("<option value=\"-\">-</option>");
                                            }
                            echo(
                                        "</select>
                                    </th>

                                    <th class=\"abinum\" id=\"".$subject."\">
                                        <select id=\"".$subject."ABI\" class=\"semesterauswahl\" name=\"".$subject."ABI\" onchange=\"reverseSelection(this.id)\"");
                                            if(in_array($subject, $nonExamSubjects)) {
                                                echo("hidden=\"hidden\"");
                                            }
                                            echo(">
                                            <option>-</option>");
                                            if(in_array($subject, $lkSubjects))
                                            {
                                                echo("<option value=\"lk1\">1. LK</option>
                                                <option value=\"lk2\">2. LK</option>");
                                            }
                                            echo("<option value=\"gks\">3. GKS</option>
                                            <option value=\"gkm\">4. GKM</option>");
                                        
                                            
                            echo("
                                        </select>
                                    </th>
                                </tr>"
                            );
                        }

                        if($loadfromdatabase) {
                            for($i = 0; $i < count($selectiontypes); $i++) {
                                $semester = getSemester($i);
                                $j = 0;
                                while(isset($selectiontypes[$i]) && $j < count($selectiontypes[$i])) {
                                    echo("<script>setSelection(\"".$subjectnames[$j].$semester."\", \"".strtolower($selectiontypes[$i][$j])."\")</script>");
                                    $j++;
                                }
                            } 

                            $abisubs = explode(",", substr($dbConn->getAbifaecher($student), 1, -1));
                            $abitypes = ["lk1", "lk2", "gks", "gkm"];
                            for($i = 0; $i < count($abisubs); $i++) {
                                if($abisubs[$i] != "-1") {
                                    echo("<script>setSelection(\"".$subjectnames[intval($abisubs[$i])]."ABI\", \"".$abitypes[$i]."\");</script>");
                                }
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

                    <div style="text-align: center;">
                        <button class="saveButton" type="submit" name="subpage" value="save">Speichern</button>
                        <br>
                        <?php
                            if(isset($_POST["subpage"])&&($_POST["subpage"]=="save")) {
                                echo("<div id=\"printButton\"><button class=\"saveButton\" type=\"submit\" name=\"subpage\" value=\"print\">Drucken</button></div>");
                                
                                $abisubs = explode(";", urldecode($_COOKIE["abi"]));
                                $abitypes = ["lk1", "lk2", "gks", "gkm"];
                                for($i = 0; $i < count($abisubs); $i++) {
                                    if($abisubs[$i] != -1) {
                                        echo("<script>setSelection(\"".$subjectnames[$abisubs[$i]]."ABI\", \"".$abitypes[$i]."\");</script>");
                                    }
                                }
                            }
                        ?>
                    </div>
                </div>
            </div>
        </form>

        <!--links to Impressum and additional information-->
        <div class="infoBar">
            <div class="infoStandard"> 
                <a href="impressum.php" id="imprintLink">Impressum</a>
            </div>
        </div>
    </body>
</html>

<!-- JS Methods -->

<script>
    function reverseSelection(id) {
        let subject = id.slice(0, id.length-3);
        abiselect = document.getElementById(subject + "ABI");
        if(abiselect.value == "lk1" || abiselect.value == "lk2") {
            for(i = 2; i < 6; i++) {
                document.getElementById(subject + getSemester(i)).value = "lk";
            }
        }
        if(abiselect.value == "gks" || abiselect.value == "gkm") {
            for(i = 2; i < 6; i++) {
                document.getElementById(subject + getSemester(i)).value = "s";
            }
        }
    }

    function loadSelectedSubs() {
        var allSubs = <?php echo '["' . implode('", "', $subjectnames) . '"]' ?>;
        var selectedSubs = <?php echo '["' . implode('", "', $subjectvalues) . '"]'?>;

        let subjectTags = getCookie("subjectTags").split(";");
        for(i = 0; i < allSubs.length; i++) {
            if(!selectedSubs.includes(allSubs[i]) &&  !subjectTags[i].includes("pfl")) {
                document.getElementById(allSubs[i] + "EF1").value = "-";
                document.getElementById(allSubs[i] + "EF2").value = "-";
                document.getElementById(allSubs[i] + "Q11").value = "-";
                document.getElementById(allSubs[i] + "Q12").value = "-";
                document.getElementById(allSubs[i] + "Q21").value = "-";
                document.getElementById(allSubs[i] + "Q22").value = "-";

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
                        var selectedValue = document.getElementById(allSubs[j] + getSemester(i)).value;
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

    function selectAllSemesters(id) {
        $(document).ready(function(){
            var allSubs = <?php echo '["' . implode('", "', $subjectnames) . '"]' ?>;
            //handle hochschreiben
            if(document.getElementById("selectAll").checked) {
                var selectValue = document.getElementById(id).value;
                let semesters = ["EF1", "EF2", "Q11", "Q12", "Q21", "Q22"];
                let semester = semesters.indexOf(id.slice(id.length-3));
                let subject = id.slice(0, id.length-3);
                let abiselect = document.getElementById(subject + "ABI");

                for(i = semester; i < semesters.length; i++) {

                    if(i == 5 && selectValue == "s" && abiselect.value != "gks" && abiselect.value != "gkm"){
                        document.getElementById(subject + semesters[i]).value = "m";
                    }
                    else {
                        document.getElementById(subject + semesters[i]).value = selectValue;
                    } 
                }
                selectionToCookies();
                calculateLessons();
                checkSelection();
            }

            //handle lk
            if(document.getElementById(id).value == "lk") {
                var selectValue = document.getElementById(id).value;
                let semesters = ["Q11", "Q12", "Q21", "Q22"];
                let subject = id.slice(0, id.length-3);

                for(i = 0; i < semesters.length; i++) {
                    document.getElementById(subject + semesters[i]).value = selectValue;
                }
                //check if LK1 is already selected
                let lk1Selected = false;
                allSubs.forEach((sub) => {
                    if(document.getElementById(sub + "ABI").value === "lk1") lk1Selected = true;
                });
                if(lk1Selected === true) {
                    document.getElementById(subject + "ABI").value = "lk2";
                }
                else {
                    document.getElementById(subject + "ABI").value = "lk1";
                }
                selectionToCookies();
                calculateLessons();
                checkSelection();
            }
        })
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
        let abiTypes = new Array("1. LK", "2. LK", "3. GKS", "4. GKM");

        // create empty selection https://stackoverflow.com/a/52268285 and empty abi array
        let selection = new Array(6).fill(0).map(() => new Array(subjects.length).fill(0));
        let abi = new Array(4).fill(-1);

        // read selection from table in web page
        var table = document.getElementById("selection");

        // skip first and last row (column title and weekly hours)
        for (let i = 1; i < table.rows.length - 1; i++) {
            // find index of subject
            let indexSubject = 0;
            while (indexSubject < subjects.length && subjects[indexSubject] != table.rows[i].cells[0].innerHTML) {indexSubject++}
            if (subjects[indexSubject] != table.rows[i].cells[0].innerHTML) continue;

            // skip first (row title)
            for (let j = 1; j < 8; j++) {
                select = table.rows[i].cells[j].children[0];

                if (j < 7) {// selected subject
                    // find index of selectionType
                    let indexSelType = 0;
                    while (indexSelType < selectionTypes.length && selectionTypes[indexSelType] != select.options[select.selectedIndex].innerHTML) {indexSelType++}
                    if (selectionTypes[indexSelType] != select.options[select.selectedIndex].innerHTML) continue;

                    selection[j - 1][indexSubject] = indexSelType;
                } else {// abi subjects
                    // find index of abi type
                    let indexAbiType = 0;
                    while (indexAbiType < abiTypes.length && abiTypes[indexAbiType] != select.options[select.selectedIndex].innerHTML) {indexAbiType++}
                    if (abiTypes[indexAbiType] != select.options[select.selectedIndex].innerHTML) continue;

                    abi[indexAbiType] = indexSubject;
                }
                
            }
        }

        // save selection in cookies
        document.cookie = "selection=" + encodeURIComponent(selection.join(";"));
        document.cookie = "abi=" + encodeURIComponent(abi.join(";"));
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