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
                <button type="submit" name="subpage" value="third" class="backButton">Zurück</button>
                <button onclick="updateValues()" type="button" class="nextButton">Speichern</button>

                <!-- subject table-->
                <div id=faecherInfoBig>
                    <h3 class="underlined">Fächer:</h3>
                </div>
                <div class="faecherTabelleBig">
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
                    semesters.forEach((semester) => {
                        let checkbox = document.createElement('input');
                        checkbox.type = "checkbox";
                        checkbox.checked = (semester === "J");
                        belegbarkeit.appendChild(checkbox);
                    });
                    //aufgabenfeld
                    let aufgabenfeld = row.insertCell(2);
                    let select = document.createElement("select");
                    select.classList.add("agfSelect");
                    let options = ["Sprachliches","Gesellschaftswiss.","Naturwissenschaftl.","Anderes"];
                    let classifier = ["slk","gws","mnt","son"];
                    options.forEach((optionName, index) => {
                        let option = document.createElement("option");
                        option.value = classifier[index];
                        option.innerHTML = optionName;
                        console.log(fach[3]);
                        if(fach[3].includes(classifier[index])) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });
                    aufgabenfeld.appendChild(select);
                    //lk option
                    let lkOption = row.insertCell(3);
                    let lkCheck = document.createElement('input');
                    lkCheck.type = "checkbox";
                    lkCheck.id = "centered";
                    lkCheck.checked = (fach[3].includes("lks"));
                    lkOption.appendChild(lkCheck);
                    //abi option
                    let abiOption = row.insertCell(4);
                    let abiCheck = document.createElement('input');
                    abiCheck.type = "checkbox";
                    abiCheck.id = "centered";
                    abiCheck.checked = (fach[3].includes("abs"));
                    abiOption.appendChild(abiCheck);
                    //just oral option
                    let nmdlOption = row.insertCell(5);
                    let nmdlCheck = document.createElement('input');
                    nmdlCheck.type = "checkbox";
                    nmdlCheck.id = "centered";
                    nmdlCheck.checked = (fach[3].includes("nmd"));
                    nmdlOption.appendChild(nmdlCheck);
                });
            }

            function updateValues() {
                const subjectTable = document.getElementById('subjectTable');
                let faecher = JSON.parse(localStorage.getItem('faecher'));
                const tableRows = subjectTable.children[0].children;
                let titleRow = true;
                let index = 0;
                for(let subject of tableRows) {
                    if(titleRow === true) {
                        titleRow = false;
                    }
                    else {
                        const columns = subject.children;
                        //Belegbarkeit
                        const checks = columns[1].children;
                        const checkValues = new Array();
                        for(let box of checks) {
                            if(box.checked) {
                                checkValues.push("J");
                            }
                            else {
                                checkValues.push("N");
                            }
                        }
                        faecher[index][2] = new Array();
                        faecher[index][2].push(...checkValues);

                        //tags
                        const tags = new Array();
                        //Aufgabenfeld
                        const feld = columns[2];
                        const select = feld.children[0];
                        let selection = select.value;
                        tags.push(selection);
                        //lk möglich
                        const lkmoeglich = columns[3];
                        const lkbox = lkmoeglich.children[0];
                        if(lkbox.checked) {
                            tags.push("lks");
                        }
                        //abi möglich
                        const abimoeglich = columns[4];
                        const abibox = abimoeglich.children[0];
                        if(abibox.checked) {
                            tags.push("abs");
                        }
                        //abi möglich
                        const nmdmoeglich = columns[5];
                        const nmdbox = nmdmoeglich.children[0];
                        if(nmdbox.checked) {
                            tags.push("nmd");
                        }

                        //get nonchangeable tags (fsp..) and add to new list
                        if(faecher[index][3].includes("fsp")) tags.push("fsp");
                        if(faecher[index][3].includes("nfs")) tags.push("nfs");
                        //set new tags
                        faecher[index][3] = new Array();
                        faecher[index][3].push(...tags);

                        index++;
                    }
                }
                console.log(faecher);
                localStorage.setItem('faecher', JSON.stringify(faecher));
            }
        </script>
    </body>
</html>