<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="assets/styles/style.css">
        <title>Deine Wahl - Neue Wahl erstellen</title>
        <link rel="icon" type="image/x-icon" href="assets/images/icons/logo.png">
    </head>

    <script src="modules/javascript/fileHandler.js"></script>

    <body>
        <header>
            <h1><u><b>Deine Wahl</b></u></h1>
        </header>

        <div class="mainSite">
            <form method="POST" action ="createPoll.php">
                <!--Button-->
                <button type="submit" name="subpage" value="second" class="nextButton">Weiter</button>

                <!--Text and Explanation-->
                <h2>Laden Sie die Schild Dateien hoch:</h2>
                <div id="center">
                    <button type="submit" name="subpage" value="guide" class="linkButton">Hier finden Sie eine genaue Erklärung:</button>
                </div>

                <!-- File drag and drop-->
                <!--source: https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API/File_drag_and_drop-->
                <div
                    id="drop_zone"
                    ondrop="dropHandler(event);"
                    ondragover="dragOverHandler(event);">
                    <p id="uploadText">Ziehen sie die Dateien auf dieses Feld</p>
                </div>

                <script>
                    function dropHandler(ev) {
                        // Prevent default behavior (Prevent file from being opened)
                        ev.preventDefault();
                        // Use DataTransferItemList interface to access the file(s)
                        [...ev.dataTransfer.items].forEach((item, i) => {
                            // If dropped items aren't files, reject them
                            if (item.kind === 'file') { 
                                const file = item.getAsFile();
                                FileHandler.assignFile(file);
                                //visual feedback
                                var img = document.createElement('img');
                                img.src = "assets/images/icons/fileIcon0.png";
                                document.getElementById('drop_zone').appendChild(img);
                            }
                        });

                        //once everything is uploaded - select useful data and store it in local storage to parse it to checkover
                        if(FileHandler.isNecessaryUploaded()===true) {
                            //visual feedback
                            var img = document.createElement('img');
                            img.src = "assets/images/icons/finishedIcon.png";
                            img.style.position = 'absolute';
                            img.style.top = '22%';
                            img.style.left = '22.5%';
                            img.style.width = '55%';
                            document.getElementById('drop_zone').appendChild(img);

                            //school's information
                            const schulInfo = FileHandler.readDataFile(FileHandler.Schuldaten);
                            schulInfo.then((data) => {
                                //get Information from file
                                let schulform = FileHandler.getColumn(data, "Schulform")[0];
                                let name = FileHandler.getColumn(data, "Bezeichnung1")[0]+" "+FileHandler.getColumn(data, "Bezeichnung2")[0]+" "+FileHandler.getColumn(data, "Bezeichnung3")[0];
                                let teacher = FileHandler.getColumn(data, "Beratungslehrer")[0];
                                //trim to avoid unnecessary spaces
                                name.trim();
                                schulform.trim();
                                teacher.trim();
                                //store in storage for next page to access
                                localStorage.setItem('schulname', name);
                                localStorage.setItem('schulform', schulform);
                                localStorage.setItem('beratungslehrer', teacher);
                            });

                            //pupil's information
                            const schuelerPromise = new Promise((resolve, reject) => {
                                const schueler = new Map();

                                //get all pupils from SchuelerBasisdaten
                                const pupilsInfo = FileHandler.readDataFile(FileHandler.SchuelerBasisdaten);
                                pupilsInfo.then((data) => {
                                    //get relevant columns
                                    const nachname = FileHandler.getColumn(data, "Nachname");
                                    const vorname = FileHandler.getColumn(data, "Vorname");
                                    const geburtsdatum = FileHandler.getColumn(data, "Geburtsdatum");
                                    const konfession = FileHandler.getColumn(data, "StatistikKrz Konfession");
                                    const klasse = FileHandler.getColumn(data, "Klasse");

                                    //generate Identifier column
                                    let identifier = new Array();
                                    nachname.forEach((lastname, i) => {
                                        let ident = lastname+vorname[i]+geburtsdatum[i];
                                        ident.replace('.','');
                                        identifier[i] = ident;
                                    });

                                    //put into map
                                    identifier.forEach((ident, i) => {
                                        schueler.set(ident, new Array(nachname[i], vorname[i], konfession[i], klasse[i], new Array()));
                                    });

                                    //add foreign language information
                                    const languageInfo = FileHandler.readDataFile(FileHandler.SchuelerSprachenfolgen);
                                    languageInfo.then((data) => {
                                        //get relevant columns
                                        const nachname = FileHandler.getColumn(data, "Nachname");
                                        const vorname = FileHandler.getColumn(data, "Vorname");
                                        const geburtsdatum = FileHandler.getColumn(data, "Geburtsdatum");
                                        const reihenfolge = FileHandler.getColumn(data, "Reihenfolge");
                                        const fach = FileHandler.getColumn(data, "Statistik-Fach");
                                        const abJahr = FileHandler.getColumn(data, "Jahrgang von");
                                        const abQuartal = FileHandler.getColumn(data, "Abschnitt von");
                                        const bisJahr = FileHandler.getColumn(data, "Jahrgang bis");
                                        const bisQuartal = FileHandler.getColumn(data, "Abschnitt bis");
                                        
                                        //generate Identifier column
                                        let identifier = new Array();
                                        nachname.forEach((lastname, i) => {
                                            let ident = lastname+vorname[i]+geburtsdatum[i];
                                            ident.replace('.','');
                                            identifier[i] = ident;
                                        });

                                        //add information to appropriate set
                                        identifier.forEach((ident, i) => {
                                            //get current data set
                                            let pupil = schueler.get(ident);
                                            //push read language onto language array
                                            let begin = abJahr[i]+"-";
                                            if(abQuartal[i] != "") {
                                                begin = begin + abQuartal[i];
                                            }
                                            else {
                                                begin = begin + "1";
                                            }
                                            let end = bisJahr[i]+"-";
                                            if(bisQuartal[i] != "") {
                                                end = end + bisQuartal[i];
                                            }
                                            else if(bisJahr[i] != ""){
                                                end = end + "4";
                                            }
                                            else {
                                                end = end.slice(0, -1);
                                            }
                                            const fullName = FileHandler.getSubjectName(fach[i]);
                                            fullName.then((subject) => {
                                                pupil[4].push(new Array(reihenfolge[i], subject, begin, end));
                                                schueler.set(ident, pupil);
                                                if(i === identifier.length-1) {
                                                    console.log("finished process");
                                                    resolve(schueler);
                                                }
                                            },
                                            (failure) => {
                                                let subject = "Unbekanntes Fach: "+fach[i];
                                                pupil[4].push(new Array(reihenfolge[i], subject, begin, end));
                                                schueler.set(ident, pupil);
                                                if(i === identifier.length-1) {
                                                    console.log("finished process");
                                                    resolve(schueler);
                                                }
                                            }
                                            );
                                        });
                                    });
                                });
                            });


                            //subject information
                            const subjectPromise = new Promise((resolve, reject) => {
                                const subjects = new Array();

                                //read faecher data
                                const subjectsInfo = FileHandler.readDataFile(FileHandler.Faecher);
                                subjectsInfo.then((data) => {
                                    //get relevant columns
                                    const bezeichnung = FileHandler.getColumn(data, "Bezeichnung");
                                    const intKrz = FileHandler.getColumn(data, "InternKrz");
                                    //Belegbarkeit
                                    const ef1 = FileHandler.getColumn(data, "in EF.1 belegbar");
                                    const ef2 = FileHandler.getColumn(data, "in EF.2 belegbar");
                                    const q11 = FileHandler.getColumn(data, "in Q1.1 belegbar");
                                    const q12 = FileHandler.getColumn(data, "in Q2.1 belegbar");
                                    const q21 = FileHandler.getColumn(data, "in Q3.1 belegbar");
                                    const q22 = FileHandler.getColumn(data, "in Q4.1 belegbar");
                                    //tags
                                    const lkSubj = FileHandler.getColumn(data, "als AF 1-2 belegbar");
                                    const abiSubj = FileHandler.getColumn(data, "als AF 1-4 belegbar");
                                    const justOral = FileHandler.getColumn(data, "nur mündlich");
                                    const newLang = FileHandler.getColumn(data, "als neue FS in SII belegbar");
                                    const field = FileHandler.getColumn(data, "Sortierung S2");
                                    //Leitfach
                                    const lf1 = FileHandler.getColumn(data, "Leitfach");
                                    const lf2 = FileHandler.getColumn(data, "Leitfach2");

                                    //add basic information into array
                                    bezeichnung.forEach((name, i) => {
                                        subjects.push(new Array(name, intKrz[i], new Array(ef1[i], ef2[i], q11[i], q12[i], q21[i], q22[i]), new Array(), new Array()));
                                    });

                                    //add tags
                                    subjects.forEach((subject, i) => {
                                        if(lkSubj[i] === "J") subject[3].push("lks");
                                        if(justOral[i] === "J") subject[3].push("nmd");
                                        if(abiSubj[i] === "J") subject[3].push("abs");
                                        if(newLang[i] === "J") subject[3].push("nfs");
                                    });

                                    //add "leitfächer"
                                    subjects.forEach((subject, i) => {
                                        if(lf1[i] != "") subject[4].push(lf1[i]);
                                        if(lf2[i] != "") subject[4].push(lf2[i]);
                                    });

                                    //try to guess aufgabenfeld
                                    let maths = -1;
                                    let history = -1;
                                    let sport = -1;
                                    subjects.forEach((subject, i) => {
                                        if(subject[1] === "M") {
                                            maths = parseInt(field[i]);
                                        }
                                        if(subject[1] === "GE") {
                                            history = parseInt(field[i]);
                                        }
                                        if(subject[1] === "SP") {
                                            sport = parseInt(field[i]);
                                        }
                                    });
                                    //check if subjects were found
                                    if(maths >= 0 && history >= 0 && sport >= 0) {
                                        //in that case match every accordingly
                                        subjects.forEach((subject, i) => {
                                            if(parseInt(field[i]) < history) {
                                                subject[3].push("slk");
                                            } else if (parseInt(field[i]) < maths) {
                                                subject[3].push("gws");
                                            } else if (parseInt(field[i]) < sport) {
                                                subject[3].push("mnt");
                                            } else {
                                                subject[3].push("son");
                                            }
                                        });
                                    }

                                    //foreign languages
                                    const languageSubjectsInfo = FileHandler.readDataFile(FileHandler.SprachFaecher);
                                    languageSubjectsInfo.then((data) => {
                                        const intLanguageKrz = FileHandler.getColumn(data, "InternKrz");
                                        //look for each subject if its abbreviation is listed in Sprachfaecher
                                        subjects.forEach((subject, i) => {
                                            intLanguageKrz.forEach((abbrev) => {
                                                if(abbrev === subject[1]) {
                                                    subject[3].push("fsp");
                                                }
                                            });
                                        });

                                        resolve(subjects);
                                    });
                                });
                            });

                            //save to local storage
                            schuelerPromise.then((schueler) => {
                                FileHandler.saveMapToLocal(schueler, "schueler");
                            });

                            //save to local storage
                            subjectPromise.then((faecher) => {
                                localStorage.setItem('faecher', JSON.stringify(faecher));
                            });

                            //initialize Conditions
                            conditions = new Array();
                            localStorage.setItem('bedingungen', JSON.stringify(conditions));

                            console.log(schuelerPromise);
                            console.log(subjectPromise);
                        }
                    }

                    function dragOverHandler(ev) {
                        // Prevent default behavior (Prevent file from being opened)
                        ev.preventDefault();
                    }
                </script>
            </form>
        </div>

        <div class="progressBar">
            <div class="footer-container">
                <div class="footer-segment">
                </div>

                <div class="footer-segment">
                    <div class="progress-highlighted">
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
                    <div class="progress-standard">
                        <div class=circle>3</div>
                        <p>Wahl starten!</p>
                    </div>
                </div>

                <div class="footer-segment">
                </div>
            </div>
        </div>

    </body>
</html>