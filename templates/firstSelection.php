<!DOCTYPE html>
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

    //group subjects
    $nwSubjects = array();
    $spSubjects = array();
    $gwSubjects = array();
    $soSubjects = array();

    foreach($subjects as $subject) {
        if($subject->hasTag("slk")) {
            array_push($spSubjects, $subject->getName());
        }
        else if($subject->hasTag("gws")) {
            array_push($gwSubjects, $subject->getName());
        }
        else if($subject->hasTag("mnt")) {
            array_push($nwSubjects, $subject->getName());
        }
        else {
            array_push($soSubjects, $subject->getName());
        }
    }
?>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="assets/styles/style.css">
        <title>Deine Wahl - Übersicht</title>
	<link rel="icon" type="image/x-icon" href="assets/images/icons/logo.png">
    </head>
    <!--Import jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="libaries/jquery-3.6.0.js"></script>

    <!-- Begrenzung der Anzahl Buttons -->
    <script>
        function setVisibility(id, bool) {
            if(bool) {
                document.getElementById(id).style.display = "inline-block";
            }
            else {
                document.getElementById(id).style.display = "none";
            }
        }
    </script>

    <!-- Naturwissenschaften Buttonfunktion -->
    <script>
        $(document).ready(function(){
            let nwSubs = <?php echo '["' . implode('", "', $nwSubjects) . '"]' ?>;
            $(".blockContainer").find("#addNw").click(function() {
                $(".blockContainer").find("#nwGrid").find("tbody").append($(".blockContainer").find("#nwGrid").find("tbody").find("#Row1Nw").clone().attr("id", "Row" + ($('#nwGrid tr').length + 1) + "Nw"));
                $(".blockContainer").find("#nwGrid").find("tbody").find("#Row" + $('#nwGrid tr').length + "Nw").find(".fachauswahl").attr("name", "nw" + $('#nwGrid tr').length);
                setVisibility("addNw", $('#nwGrid tr').length < nwSubs.length && $('#nwGrid tr').length < 7);
            });
        });
    </script>

    <!-- Sprachen Buttonfunktion -->
    <script>
        $(document).ready(function(){
            let spSubs = <?php echo '["' . implode('", "', $spSubjects) . '"]' ?>;
            $(".blockContainer").find("#addSp").click(function() {
                $(".blockContainer").find("#spGrid").find("tbody").append($(".blockContainer").find("#spGrid").find("tbody").find("#Row1Sp").clone().attr("id", "Row" + ($('#spGrid tr').length + 1) + "Sp"));
                $(".blockContainer").find("#spGrid").find("tbody").find("#Row" + $('#spGrid tr').length + "Sp").find(".fachauswahl").attr("name", "sp" + $('#spGrid tr').length);
                setVisibility("addSp", $('#spGrid tr').length < spSubs.length && $('#spGrid tr').length < 7);
            });
        });
    </script>

    <!-- Gesellschaftswissenschaften Buttonfunktion -->
    <script>
        $(document).ready(function(){
            let gwSubs = <?php echo '["' . implode('", "', $gwSubjects) . '"]' ?>;
            $(".blockContainer").find("#addGw").click(function() {
                $(".blockContainer").find("#gwGrid").find("tbody").append($(".blockContainer").find("#gwGrid").find("tbody").find("#Row1Gw").clone().attr("id", "Row" + ($('#gwGrid tr').length + 1) + "Gw"));
                $(".blockContainer").find("#gwGrid").find("tbody").find("#Row" + $('#gwGrid tr').length + "Gw").find(".fachauswahl").attr("name", "gw" + $('#gwGrid tr').length);
                setVisibility("addGw", $('#gwGrid tr').length < gwSubs.length && $('#gwGrid tr').length < 7);
            });
        });
    </script>

    <!-- Sonstige Fächer Buttonfunktion -->
    <script>
        $(document).ready(function(){
            let soSubs = <?php echo '["' . implode('", "', $soSubjects) . '"]' ?>;
            $(".blockContainer").find("#addSo").click(function() {
                $(".blockContainer").find("#soGrid").find("tbody").append($(".blockContainer").find("#soGrid").find("tbody").find("#Row1So").clone().attr("id", "Row" + ($('#soGrid tr').length + 1) + "So"));
                $(".blockContainer").find("#soGrid").find("tbody").find("#Row" + $('#soGrid tr').length + "So").find(".fachauswahl").attr("name", "so" + $('#soGrid tr').length);
                setVisibility("addSo", $('#soGrid tr').length < soSubs.length && $('#soGrid tr').length < 7);
            });
        });
    </script> 

    <body class=warmBackground>
        <header>
            <form method="post">
                <button type="submit" name="subpage" value="main" class="headerLink"><h1><u><b>Deine Wahl</b></u></h1></button>
            </form>
        </header>

		<div class="blockContainer">
            <form method="POST" action ="selection.php">
                <!-- Naturwissenschaften-->
		        <div class="block" id="topLeft">
			        <div id="Naturwissenschaften">
                        <div id=center><p><b class="subtitleColored">Naturwissenschaften</b></p></div>
                        <br>
                        <table id="nwGrid">
                            <tbody id = "nwBody">
                                <tr id = "Row1Nw">
                                    <td>
                                        <div id=center><select name="nw1" class="fachauswahl">
                                            <option value="empty" selected disabled hidden>--Bitte Fach auswählen--</option>
                                            <?php
                                                foreach($nwSubjects as $nw) {
                                                    echo("<option value=\"".$nw."\">".$nw."</option>");
                                                }
                                            ?>
                                        </select></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div id=center>
                            <button type="button" id="addNw" class=roundButton>+</button>
                        </div>
		            </div>
		        </div>
		  
                <!-- Sprachen-->
		        <div class="block"  id="topRight">
			        <div id="Sprachen">
                        <div id=center><p><b class="subtitleColored">Sprachen und Künste</b></p></div>
                        <br>
                        <table id="spGrid">
                            <tbody id = "spBody">
                                <tr id = "Row1Sp">
                                    <td>
                                        <div id=center><select name="sp1" class="fachauswahl">
                                            <option value="empty" selected disabled hidden>--Bitte Fach auswählen--</option>
                                            <?php
                                                foreach($spSubjects as $sp) {
                                                    echo("<option value=\"".$sp."\">".$sp."</option>");
                                                }
                                            ?>
                                        </select></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
					    <div id=center><button type="button" id="addSp" class=roundButton>+</button></div>
			        </div>
		        </div>
		  
                <!-- Gesellschaftswissenschaften -->
                <div class="block"  id="bottomLeft">
			        <div id="GesellschaftsWis">
                        <div id=center><p><b class="subtitleColored">Gesellschaftswis.</b></p></div>
                        <br>
                        <table id="gwGrid">
                            <tbody id = "gwBody">
                                <tr id = "Row1Gw">
                                    <td>
                                        <div id=center><select name="gw1" class="fachauswahl">
                                            <option value="empty" selected disabled hidden>--Bitte Fach auswählen--</option>
                                            <?php
                                                foreach($gwSubjects as $gw) {
                                                    echo("<option value=\"".$gw."\">".$gw."</option>");
                                                }
                                            ?>
                                        </select></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
					    <div id=center><button type="button" id="addGw" class=roundButton>+</button></div>
                    </div>
		        </div>
		  
                <!-- Musische und weiter Fächer -->
		        <div class="block" id="bottomRight">
			        <div id="Weitere_Bereiche">
                        <div id=center><p><b class="subtitleColored">Weitere Bereiche</b></p></div>
                        <br>
                        <table id="soGrid">
                            <tbody id = "soBody">
                                <tr id = "Row1So">
                                    <td>
                                        <div id=center><select name="so1" class="fachauswahl">
                                            <option value="empty" selected disabled hidden>--Bitte Fach auswählen--</option>
                                            <?php
                                                foreach($soSubjects as $so) {
                                                    echo("<option value=\"".$so."\">".$so."</option>");
                                                }
                                            ?>
                                        </select></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
					    <div id=center><button type="button" id="addSo" class=roundButton>+</button></div>
                    </div>
		        </div>

                <button type="submit" name="subpage" value="table" class="stdButton" id=firstToTable>Zur Gesamtübersicht  &#x2192</button>
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
