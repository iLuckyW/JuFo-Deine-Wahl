class FileHandler {
    constructor() {
        const currentDate = new Date();
        this.timestamp = currentDate.getHours()+":"+currentDate.getMinutes()+":"+currentDate.getSeconds()+":"+currentDate.getDate()+":"+(currentDate.getMonth()+1)+":"+currentDate.getFullYear();
    }

    static Faecher;
    static FaecherProfile;
    static NichtMoeglicheFaecherKombinationen;
    static Schueler_IDs;
    static SchuelerBasisdaten;
    static SchuelerLeistungsdaten;
    static SchuelerSprachenfolgen;
    static Schuldaten;
    static SprachFaecher;

    //assigns a File to the appropriate static field
    static assignFile(file) {
        switch(file.name) {
            case "Faecher.dat":
                FileHandler.Faecher = file;
                break;
            
            case "FaecherProfile.dat":
                FileHandler.FaecherProfile = file;
                break;

            case "NichtMoeglicheFaecherKombinationen.dat":
                FileHandler.NichtMoeglicheFaecherKombinationen = file;
                break;
            
            case "Schueler_IDs.dat":
                FileHandler.Schueler_IDs = file;
                break;

            case "SchuelerLeistungsdaten.dat":
                FileHandler.SchuelerLeistungsdaten = file;
                break;
            
            case "SchuelerSprachenfolgen.dat":
                FileHandler.SchuelerSprachenfolgen = file;
                break;

            case "SchuelerBasisdaten.dat":
                FileHandler.SchuelerBasisdaten = file;
                break;

            case "Schuldaten.dat":
                FileHandler.Schuldaten = file;
                break;
            
            case "SprachFaecher.dat":
                FileHandler.SprachFaecher = file;
                break;
        }
    }

    static isNecessaryUploaded() {
        var count = 0;
        const missing = new Array();

        if (FileHandler.Faecher!=null) {
            count++;
        }
        else {
            missing.push("Faecher");
        }

        if (FileHandler.Schueler_IDs!=null){
            count++;
        }
        else {
            missing.push("Schueler_IDs");
        }

        if (FileHandler.SchuelerBasisdaten!=null){
            count++;
        }
        else {
            missing.push("SchuelerBasisdaten");
        }

        if (FileHandler.SchuelerSprachenfolgen!=null){
            count++;
        }
        else {
            missing.push("SchuelerSprachenfolgen");
        }

        if (FileHandler.Schuldaten!=null){
            count++;
        }
        else {
            missing.push("Schuldaten");
        }

        if (FileHandler.SprachFaecher!=null){
            count++;
        }
        else {
            missing.push("SprachFaecher");
        }
        
        if(count === 6) {
            return true;
        }
        else {
            return missing;
        }
    }

    //reads the content of a .dat file and returns an two dimensional array with its contents - Each column is an own subarray with its title in the first field
    static readDataFile(file) {
        const promise = new Promise(function(resolved, rejected) {
            const result = new Array();
            const text = file.text();
            text.then((contents) => {
                //split text into lines and take the columns from the first line
                let lines = contents.split(/\r\n|\n|\r/g);
                const columns = lines[0].split('|');
                //create the columns
                columns.forEach((column) => {
                    result.push(new Array(column));
                });
                //remove first line to only look at data
                lines.shift();
                //add the values from the lines to the approriate array column
                lines.forEach((line) => {
                    const dataInLine = line.split('|');
                    dataInLine.forEach((value, index) => {
                        result[index].push(value);
                    });
                });

                //remove unfinished line at end
                let minLength = result[0].length;
                result.forEach((column) => {
                    if(column.length < minLength) {
                        minLength = column.length;
                    }
                });
                result.forEach((column) => {
                    while(column.length > minLength) {
                        column.pop();
                    }
                });

                //return result
                resolved(result);
            });
        });
        return promise;
    }

    //saves map to local storage with storageName as name after stringifying it - source for replace: https://stackoverflow.com/questions/29085197/how-do-you-json-stringify-an-es6-map (15.10.2022)
    static saveMapToLocal(map, storageName) {
        const stringVersion = JSON.stringify(map, (key, value) => {
            if(value instanceof Map) {
                return {
                dataType: 'Map',
                value: Array.from(value.entries()), // or with spread: value: [...value]
                };
            } else {
                return value;
            }
        });
        localStorage.setItem(storageName, stringVersion);
    }

    //retrieves map with name storageName from local storage source for reviver: https://stackoverflow.com/questions/29085197/how-do-you-json-stringify-an-es6-map
    static retrieveMapFromLocal(storageName) {
        const stringVersion = localStorage.getItem(storageName);
        const result = JSON.parse(stringVersion, (key, value) => {
            if(typeof value === 'object' && value !== null) {
                if (value.dataType === 'Map') {
                return new Map(value.value);
                }
            }
            return value;
        });
        return result;
    }

    //searches through the data and returns the values in the column with "columnName"
    static getColumn(data, columnName) {
        let result;
        data.forEach((column) => {
            if(column[0].localeCompare(columnName) == 0) {
                result = column;
            }
        })
        if(result != null) {
            result.shift();
            return result;
        }
        else {
            return new Array("failure");
        }
    }

    //returns the full Name of the subject with abbreviation "abbreviation" 
    static getSubjectName(abbreviation) {
        const promise = new Promise(function(resolved, rejected) {
            //read data and select relevant columns
            const faecherInfo = FileHandler.readDataFile(FileHandler.Faecher);
            faecherInfo.then((data) => {
                let intKrz = FileHandler.getColumn(data, "InternKrz");
                let staKrz = FileHandler.getColumn(data, "StatistikKrz");
                let fullName = FileHandler.getColumn(data, "Bezeichnung");
                //try to match with staKrz and then with intKrz
                let index = intKrz.indexOf(abbreviation);
                if(index >= 0) {
                    resolved(fullName[index]);
                }
                index = staKrz.indexOf(abbreviation);
                if(index >= 0) {
                    resolved(fullName[index]);
                }
                //if both have failed try again without numbers source: https://stackoverflow.com/questions/4993764/how-to-remove-numbers-from-a-string
                let withoutNumbers = abbreviation.replace(/[0-9]/g, '');
                index = staKrz.indexOf(withoutNumbers);
                if(index >= 0) {
                    resolved(fullName[index]);
                }
                index = intKrz.indexOf(withoutNumbers);
                if(index >= 0) {
                    resolved(fullName[index]);
                }
                //when there is no match found try only the first the char
                let firstChar = abbreviation.charAt(0);
                index = staKrz.indexOf(firstChar);
                if(index >= 0) {
                    resolved(fullName[index]);
                }
                index = intKrz.indexOf(firstChar);
                if(index >= 0) {
                    resolved(fullName[index]);
                }
                //else reject
                rejected("Full subject name not found to: " + abbreviation);
            }); 
        });

        return promise;
    }
}