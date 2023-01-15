<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="assets/styles/style.css">
        <title>Deine Wahl - Neue Wahl erstellen</title>
    </head>

    <script src="modules/javascript/fileHandler.js"></script>
    <script src="modules/javascript/tileHandler.js"></script>

    <body onload="insertValues()">
        <header>
            <h1><u><b>Deine Wahl</b></u></h1>
        </header>

        <div class="mainSite">
            <form onsubmit="updateValues()" method="POST" action ="createPoll.php">
                <button type="submit" name="subpage" value="second" class="backButton">Zurück</button>

                <!-- subject table-->
                <div id=faecherInfo>
                    <h3 class="underlined">Fächer:</h3>
                </div>
                <div class="faecherTabelle">
                    <table class="overviewTable" id="subjectTable">
                        <!--Headline-->
                        <tr class="overviewRow">
                            <th class="overviewValue">Name</th>
                            <th class="overviewValue">Belegbarkeit</th>
                            <th class="overviewValue">Aufgabenfeld</th>
                            <th class="overviewValue">LK möglich</th>
                            <th class="overviewValue">Abifach möglich</th>
                            <th class="overviewValue">nur mündlich</th>
                        </tr>
                    </table>
                </div>

                <button type="submit" name="subpage" value="subjects" class="backButton" id="editSubjects">Bearbeiten</button>

                <!--Condition definition-->
                <div id=bedingungenInfo>
                    <h3 class="underlined">Bedingungen:</h3>
                </div>
                <div id="conditionField">
                    <div id="barrier">

                    </div>
                </div>

                <button type="submit" name="subpage" value="conditions" class="backButton" id="editConditions">Bearbeiten</button>
            </form>
            <form onsubmit="uploadValues()" method="POST" action ="createPoll.php">
                <button type="submit" name="subpage" value="finished" id="verifyStart">Wahl Starten!</button>
                <!--data transmission into post variables-->
                <input type="hidden" id="schueler" name="schueler">
                <input type="hidden" id="faecher" name="faecher">
                <input type="hidden" id="schulname" name="schulname">
                <input type="hidden" id="schulform" name="schulform">
                <input type="hidden" id="lehrerName" name="lehrerName">
                <input type="hidden" id="bedingungen" name="bedingungen">
            </form>
        </div>

        <div class="progressBar">
            <div class="footer-container">
                <div class="footer-segment">
                </div>

                <div class="footer-segment">
                    <div class="progress-standard">
                        <div class=circle>1</div>
                        <p>Datei hochladen</p>
                    </div>
                </div>

                <div class="footer-segment">
                    <div class="progress-standard">
                        <div class=circle >2</div>
                        <p>Daten vervollständigen</p>
                    </div>
                </div>

                <div class="footer-segment">
                    <div class="progress-highlighted">
                        <div class=circle>3</div>
                        <p>Wahl starten!</p>
                    </div>
                </div>

                <div class="footer-segment">
                </div>
            </div>
        </div>

        <script>
            function insertValues() {
                //subject data
                const faecher = JSON.parse(localStorage.getItem('faecher'));
                const subjectTable = document.getElementById('subjectTable');
                //insert a line for each subject
                faecher.forEach((fach) => {
                    //row definition
                    let row = subjectTable.insertRow(-1);
                    row.className = 'overviewRow';
                    //insert cells
                    let name = row.insertCell(0);
                    name.className = 'overviewValue';
                    name.innerHTML = fach[0];
                    let belegbarkeit = row.insertCell(1);
                    belegbarkeit.className = 'overviewValue';
                    //generate checkboxes for each semester
                    let semesters = fach[2];
                    belegbarkeit.innerHTML = "";
                    belegbarkeit.classList.add("centeredText");
                    semesters.forEach((semester) => {
                        if(semester === "J") {
                            belegbarkeit.innerHTML = belegbarkeit.innerHTML + "&#x2611";
                        }
                        else {
                            belegbarkeit.innerHTML = belegbarkeit.innerHTML + "&#x2610";
                        }
                    });
                    //aufgabenfeld
                    let aufgabenfeld = row.insertCell(2);
                    aufgabenfeld.className = 'overviewValue';
                    aufgabenfeld.classList.add("centeredText");
                    let options = ["Sprachliches","Gesellschaftswiss.","Naturwissenschaftl.","Anderes"];
                    let classifier = ["slk","gws","mnt","son"];
                    options.forEach((optionName, index) => {
                        if(fach[3].includes(classifier[index])) {
                            aufgabenfeld.innerHTML = optionName;
                        }
                    });
                    //checkboxxes
                    //lk option
                    let lkOption = row.insertCell(3);
                    lkOption.className = 'overviewValue';
                    lkOption.classList.add("centeredText");
                    if(fach[3].includes("lks")){
                        lkOption.innerHTML ="&#x2611";
                    }
                    else {
                        lkOption.innerHTML ="&#x2610";
                    }
                    //abi option
                    let abiOption = row.insertCell(4);
                    abiOption.className = 'overviewValue';
                    abiOption.classList.add("centeredText");
                    if(fach[3].includes("abs")){
                        abiOption.innerHTML ="&#x2611";
                    }
                    else {
                        abiOption.innerHTML ="&#x2610";
                    }
                    //just oral option
                    let nmdlOption = row.insertCell(5);
                    nmdlOption.className = 'overviewValue';
                    nmdlOption.classList.add("centeredText");
                    if(fach[3].includes("nmd")){
                        nmdlOption.innerHTML ="&#x2611";
                    }
                    else {
                        nmdlOption.innerHTML ="&#x2610";
                    }
                });

                //print already created condtions
                const bedingungen = JSON.parse(localStorage.getItem('bedingungen'));
                const conField = document.getElementById('conditionField');
                bedingungen.forEach((bedingung) => {
                    let text = document.createElement('p');
                    text.innerHTML = "";
                    bedingung.forEach((word) => {
                        text.innerHTML = text.innerHTML + TileHandler.getText(word) + " ";
                    });
                    conField.appendChild(text);
                    conField.appendChild(document.createElement('br'));
                });
                //add message if nothing was selecte yet
                if(bedingungen.length === 0) {
                    let text = document.createElement('p');
                    text.innerHTML = "Es wurden noch keinen Bedingungen festgelegt";
                    conField.appendChild(text);
                }
            }

            function updateValues() {

            }

            function uploadValues() {
                //load edits
                updateValues();
                
                //put values into fields
                const schuelerMap = FileHandler.retrieveMapFromLocal('schueler');
                //convert map into array
                const schuelerArray = new Array();
                schuelerMap.forEach((element) => {
                    schuelerArray.push(element);
                });
                //convert into string for input field;
                const schuelerString = JSON.stringify(schuelerArray);
                document.getElementById('schueler').value = schuelerString;
                //put faecher into field
                document.getElementById('faecher').value = localStorage.getItem("faecher");
                //school's information
                document.getElementById('schulname').value = localStorage.getItem('schulname');
                document.getElementById('schulform').value = localStorage.getItem('schulform');
                document.getElementById('lehrerName').value = localStorage.getItem('beratungslehrer');
                document.getElementById('bedingungen').value = localStorage.getItem('bedingungen');
            }
        </script>
    </body>
</html>