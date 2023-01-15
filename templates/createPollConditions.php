<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="assets/styles/style.css">
        <title>Deine Wahl - Neue Wahl erstellen</title>
        <link rel="icon" type="image/x-icon" href="assets/images/icons/logo.png">
    </head>

    <script src="modules/javascript/fileHandler.js"></script>
    <script src="modules/javascript/tileHandler.js"></script>

    <body onload="insertValues()">
        <header>
            <h1><u><b>Deine Wahl</b></u></h1>
        </header>

        <div class="mainSite">
            <form onsubmit="updateValues()" method="POST" action ="createPoll.php">
                <button type="submit" name="subpage" value="third" class="backButton">Zurück</button>
                <button onclick="save()" type="button" class="nextButton">Speichern</button>

                <!--Condition definition-->
                <div id=bedingungenInfoBig>
                    <h3 class="underlined">Bedingungen:</h3>
                </div>
                <div id="conditionFieldBig">
                    <div id="barrier">
                    </div>

                    <!--blocks for condition statements-->
                    <!--starters-->
                    <div class="draggable start" id="min0">
                        <p>Mindestens</p>
                    </div>

                    <div class="draggable start" id="max0">
                        <p>Maximal</p>
                    </div>

                    <div class="draggable start" id="equ0">
                        <p>Genau</p>
                    </div>
                    <!--values-->
                    <div class="draggable mid" id="num0">
                        <input type="number"  value="0">
                    </div>
                    <!--endings-->
                    <div class="draggable end" id="cou0">
                        <p>Kurse</p>
                    </div>

                    <div class="draggable end" id="les0">
                        <p>Wochenstunden</p>
                    </div>
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
                //initialize condition field
                new TileHandler(document);
                draggables = document.getElementsByClassName("draggable");
                console.log(draggables);
                for (let element of draggables) {
                    TileHandler.makeDraggable(element);
                }
                //load stored conditions
                const bedingungen = JSON.parse(localStorage.getItem('bedingungen'));
                bedingungen.forEach((bedingung) => {
                    TileHandler.instantiateConstruct(bedingung);
                });
            }

            function updateValues() {
                conditions = TileHandler.returnConstructs();
                localStorage.setItem('bedingungen', JSON.stringify(conditions));
            }

            function save() {
                conditions = TileHandler.returnConstructs();
                localStorage.setItem('bedingungen', JSON.stringify(conditions));
            }
        </script>
    </body>
</html>