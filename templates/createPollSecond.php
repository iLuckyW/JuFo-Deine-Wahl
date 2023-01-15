<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="assets/styles/style.css">
        <title>Deine Wahl - Neue Wahl erstellen</title>
    </head>

    <script src="modules/javascript/fileHandler.js"></script>

    <body onload="insertValues()">
        <header>
            <h1><u><b>Deine Wahl</b></u></h1>
        </header>

        <div class="mainSite">
            <form onsubmit="updateValues()" method="POST" action ="createPoll.php">
                <!-- Buttons to go back/forward-->
                <button type="submit" name="subpage" value="first" class="backButton">Zurück</button>
                <button type="submit" name="subpage" value="third" class="nextButton">Weiter</button>
                <!--Überschrift und Infokästen-->
                <h2>Datenübersicht</h2>
                <div id=schulInfo>
                    <h3 class="underlined">Schule:</h3>

                    <label for="schulname">Name:</label>
                    <input type="text" id="schulname" class="textFieldSecond" required>
                    <br>
                    <label for="schulform">Schulform:</label>
                    <input type="text" id="schulform" class="textFieldSecond" required>
                </div>

                <div id=lehrerInfo>
                    <h3 class="underlined">Betreuende Lehrkraft:</h3>

                    <label for="lehrerName">Name:</label>
                    <input type="text" id="lehrerName" class="textFieldSecond" required>
                </div>

                <div id=schuelerInfo>
                    <h3 class="underlined">Schüler:</h3>
                </div>
                <div class="schuelerTabelle">
                    <table class="overviewTable" id="pupilTable">
                        <!--Headline-->
                        <tr class="overviewRow">
                            <th class="overviewValue">Name</th>
                            <th class="overviewValue">Vorname</th>
                            <th class="overviewValue">Klasse</th>
                            <th class="overviewValue">Fremdsprachen</th>
                        </tr>
                    </table>
                </div>
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
                    <div class="progress-highlighted">
                        <div class=circle >2</div>
                        <p>Daten vervollständigen</p>
                    </div>
                </div>

                <div class="footer-segment">
                    <div class="progress-standard">
                        <div class=circle>3</div>
                        <p>Wahl starten!</p>
                    </div>
                </div>

                <div class="footer-segment">
                </div>
            </div>
        </div>

        <script>
            //inserts the values from local Storage on page load
            function insertValues() {
                //school data
                document.getElementById('schulname').value = localStorage.getItem('schulname');
                document.getElementById('schulname').style.width = ((localStorage.getItem('schulname').length + 1)*8)+'px';
                document.getElementById('schulform').value = localStorage.getItem('schulform');
                //teacher data
                document.getElementById('lehrerName').value = localStorage.getItem('beratungslehrer');
                //pupil data
                const schueler = FileHandler.retrieveMapFromLocal('schueler');
                console.log(schueler);
                const table = document.getElementById('pupilTable');
                schueler.forEach((pupil) => {
                    //row definition
                    let row = table.insertRow(-1);
                    row.className = 'overviewRow';
                    //insert cells
                    let name = row.insertCell(0);
                    name.className = 'overviewValue';
                    name.innerHTML = pupil[0];
                    let vorname = row.insertCell(1);
                    vorname.className = 'overviewValue';
                    vorname.innerHTML = pupil[1];
                    let klasse = row.insertCell(2);
                    klasse.className = 'overviewValue';
                    klasse.innerHTML = pupil[3];
                    //foreign languages
                    let fremdsprachen = row.insertCell(3);
                    fremdsprachen.className = 'overviewValue';
                    let text = "";
                    if(pupil[4].length === 0) {
                        text = "Keine Daten vorhanden";
                    }
                    else {
                        pupil[4].forEach((language) => {
                            text = text + language[1] + " ab Klasse " + language[2] + " - "; 
                        });
                        //remove last seperator
                        text = text.slice(0, -3);
                    }
                    fremdsprachen.innerHTML = text;
                });
            }

            //updates the values in localStorage when page is being submitted
            function updateValues() {
                //school data
                localStorage.setItem('schulname', document.getElementById('schulname').value);
                localStorage.setItem('schulform', document.getElementById('schulform').value);
                //teacher data
                localStorage.setItem('beratungslehrer', document.getElementById('lehrerName').value);
            }
        </script>
    </body>
</html>