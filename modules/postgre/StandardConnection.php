<?php
//load classes
require_once "modules/dataClasses/Student.php";
require_once "modules/dataClasses/Teacher.php";
require_once "modules/dataClasses/School.php";
require_once "modules/dataClasses/Subject.php";
require_once "modules/dataClasses/SelectionType.php";

class StandardConnection {
    //Properties
    private $servername = "postgres";
    private $username = "postgres";
    private $defaultDatabase = "deinewahl";
    private $password = "1234";
    private $conn;

    const schoolTableName = "schulen";
    const subjectTableName = "faecher";
    const gradeTableName = "stufen";
    const studentTableName = "schueler";
    const usertokenTableName = "usertoken";
    const teachesTableName = "unterrichtet";
    const languageTableName = "sprachenfolgen";
    const teacherTableName = "lehrer";

    public function __construct() {
        $this->createConnection();
    }

    //
    // methods to manipulate postgre databases
    //

    //Tries to establish connection
    function createConnection() {
        $this->conn = pg_connect("host=".$this->servername." dbname=".$this->defaultDatabase." user=".$this->username." password=". $this->password)
            or die("Could not connect:".pg_last_error());
    }

    //Closes this connection
    function closeConnetion() {
        pg_close($this->conn);
    }

    //Creates Database with given name and sets it as default
    function createDatabse($name) {
        pg_query($this->conn, "CREATE DATABASE ".$name)
            or die("Could not create database:".pg_last_error());
        $this->defaultDatabase = $name;
    }

    //Changes defaultDatabase and establishes new connection
    function changeDatabse($name) {
        $this->defaultDatabase = $name;
        $this->closeConnetion;
        $this->createConnection;
    }

    //Creates table with given name and columns, columns should be of type TableColumn
    function createTable($name, ...$column) {
        $createInstruction = "CREATE TABLE ".$name." (";
        foreach ($column as $c) {
            if($c->constraint!=null){
                $createInstruction = $createInstruction.$c->name." ".$c->type." ".$c->constraint.", ";
            }
            else {
                $createInstruction = $createInstruction.$c->name." ".$c->type.", ";
            }
            
        }
        //delete comma
        $createInstruction = substr($createInstruction, 0, -2);
        $createInstruction = $createInstruction.");";
        pg_query($this->conn, $createInstruction)
            or die("Could not create table: ".pg_last_error());
    }

    //deletes table with given name
    function deleteTable($name) {
        pg_query($this->conn, "DROP TABLE IF EXISTS ".$name." CASCADE;")
            or die("Could not delete table: ".pg_last_error());
    }

    //Creates entry in given table with given values (for empty columns write null)
    function insertInto($table, ...$entry) {
        $insertInstruction = "INSERT INTO ".$table." (";
        //add columns
        foreach ($entry as $e) {
            $insertInstruction = $insertInstruction.$e->column.", ";
        }
        //delete comma
        $insertInstruction = substr($insertInstruction, 0, -2);
        //add values
        $insertInstruction = $insertInstruction.") VALUES (";
        foreach ($entry as $e) {
            $insertInstruction = $insertInstruction.$e->value.", ";
        }
        //delete comma
        $insertInstruction = substr($insertInstruction, 0, -2);
        $insertInstruction = $insertInstruction.");";
        pg_query($this->conn, $insertInstruction)
            or die("Could not insert into table: ".pg_last_error());
    }

    function updateRow($table, $identifierColumn, $identifier, ...$entry) {
        $updateInstruction = "UPDATE ".$table." SET ";
        //add columns
        foreach ($entry as $e) {
            $updateInstruction = $updateInstruction.$e->column."=".$e->value.", ";
        }
        //delete comma
        $updateInstruction = substr($updateInstruction, 0, -2);
        //append where part
        $updateInstruction = $updateInstruction." WHERE ".$identifierColumn."=".$identifier.";";
        pg_query($this->conn, $updateInstruction)
            or die("Could not insert into table: ".pg_last_error());
    }

    //
    // methods to create necesary table structure
    //

    function setupDatabase() {
        // delete old tables
        $this->deleteTable(StandardConnection::schoolTableName);
        $this->deleteTable(StandardConnection::subjectTableName);
        $this->deleteTable(StandardConnection::gradeTableName);
        $this->deleteTable(StandardConnection::studentTableName);
        $this->deleteTable(StandardConnection::usertokenTableName);
        $this->deleteTable(StandardConnection::teachesTableName);
        $this->deleteTable(StandardConnection::languageTableName);
        $this->deleteTable(StandardConnection::teacherTableName);

        // create new tables
        $this->createSchoolTable(StandardConnection::schoolTableName);
        $this->createSubjectTable(StandardConnection::subjectTableName);
        $this->createGradeTable(StandardConnection::gradeTableName, StandardConnection::schoolTableName);
        $this->createStudentTable(StandardConnection::studentTableName,  StandardConnection::gradeTableName);
        $this->createTeacherTable(StandardConnection::teacherTableName, StandardConnection::gradeTableName);
        $this->usertokenSubjectTable(StandardConnection::usertokenTableName, StandardConnection::studentTableName, StandardConnection::teacherTableName);
        $this->createTeachesTable(StandardConnection::teachesTableName, StandardConnection::schoolTableName, StandardConnection::subjectTableName);
        $this->createLanguageTable(StandardConnection::languageTableName, StandardConnection::studentTableName, StandardConnection::subjectTableName);
    }

    function createSchoolTable($pName) {
        // schulen(schulid, name, wahltypen)
        $this->createTable($pName, new TableColumn("schulid", "SERIAL", TableColumn::Primary_Key), new TableColumn("name", "VARCHAR"), new TableColumn("wahltypen", "VARCHAR(32)[]"));
    }

    function createSubjectTable($pName) {
        // faecher(fachid, name, tags)
        $this->createTable($pName, new TableColumn("fachid", "SERIAL", TableColumn::Primary_Key), new TableColumn("name", "VARCHAR"), new TableColumn("tags", "VARCHAR(4)[]"));
    }

    function createGradeTable($pName, $pSchoolTable) {
        // stufen(stufenid, bezeichnung, ↑ schule, pruefungsordnung)
        $this->createTable($pName, new TableColumn("stufenid", "SERIAL", TableColumn::Primary_Key), new TableColumn("bezeichnung", "VARCHAR"), new TableColumn("schule", "SERIAL", TableColumn::references($pSchoolTable, "schulid")), new TableColumn("pruefungsordnung", "VARCHAR"), new TableColumn("beratungslehrer", "VARCHAR"));
    }

    function createStudentTable($pName, $pGradeTable) {
        // schueler(schuelerid, nutzername, passwort, vorname, nachname, ↑ stufe, wahl, konfession)
        $this->createTable($pName, new TableColumn("schuelerid", "SERIAL", TableColumn::Primary_Key), new TableColumn("nutzername", "VARCHAR"), new TableColumn("passwort", "VARCHAR"), new TableColumn("vorname", "VARCHAR"), new TableColumn("nachname", "VARCHAR"), new TableColumn("stufe", "SERIAL", TableColumn::references($pGradeTable, "stufenid")), new TableColumn("wahl", "SMALLINT[][]"), new TableColumn("abifaecher", "SMALLINT[]"), new TableColumn("konfession", "VARCHAR(2)"));
    }

    function usertokenSubjectTable($pName, $pStudentTable, $pTeacherTable) {
        // usertoken(token, ↑ schueler, gueltigBis)
        $this->createTable($pName, new TableColumn("token", "VARCHAR", TableColumn::Primary_Key), new TableColumn("schueler", "SERIAL", TableColumn::references($pStudentTable, "schuelerid")), new TableColumn("lehrer", "SERIAL", TableColumn::references($pTeacherTable, "lehrerid")), new TableColumn("gueltigBis", "TIMESTAMP"));
    }

    function createTeachesTable($pName, $pSchoolTable, $pSubjectTable) {
        // unterrichtet(↑ schule, ↑ fach)
        $this->createTable($pName, new TableColumn("schule", "SERIAL", TableColumn::references($pSchoolTable, "schulid")), new TableColumn("fach", "SERIAL", TableColumn::references($pSubjectTable, "fachid")), new TableColumn(TableColumn::Primary_Key, "(schule, fach)"));
    }

    function createLanguageTable($pName, $pStudentTable, $pSubjectTable) {
        // sprachenfolgen(↑ schueler, reihenfolge, ↑ fach, angewaehlt, abgewaehlt)
        $this->createTable($pName, new TableColumn("schueler", "SERIAL", TableColumn::references($pStudentTable, "schuelerid")), new TableColumn("reihenfolge", "SERIAL"), new TableColumn("fach", "SERIAL", TableColumn::references($pSubjectTable, "fachid")), new TableColumn("angewaehlt", "REAL"), new TableColumn("abgewaehlt", "REAL"), new TableColumn(TableColumn::Primary_Key, "(schueler, reihenfolge)"));
    }

    function createTeacherTable($pName, $pGradeTable) {
        // schueler(schuelerid, nutzername, passwort, vorname, nachname, ↑ stufe, wahl, konfession)
        $this->createTable($pName, new TableColumn("lehrerid", "SERIAL", TableColumn::Primary_Key), new TableColumn("nutzername", "VARCHAR"), new TableColumn("passwort", "VARCHAR"), new TableColumn("name", "VARCHAR"), new TableColumn("stufe", "SERIAL", TableColumn::references($pGradeTable, "stufenid")));
    }

    //
    // methods to insert php objects into table
    //

    function insertSchool($pSchool) {
        // check parameter
        if (!$pSchool instanceof School) throw new Exception("school needs to be a school-object");

        // convert selections types (array) to sql style
        $strSelectionTypes = "ARRAY[";
        for($i = 0; $i < count($pSchool->getSelectionTypes()); $i++) {
            $strSelectionTypes .= "'".$pSchool->getSelectionTypes()[$i]->toString()."', ";
        }
        //replace "," with "]"
        $strSelectionTypes = substr($strSelectionTypes, 0, -2)."]";

        $this->insertInto(StandardConnection::schoolTableName, new Entry("name", "'".$pSchool->getName()."'"), new Entry("wahltypen", $strSelectionTypes));
        // refrence subjects (create if not existing)
        for ($i = 0; $i < count($pSchool->getSubjects()); $i++) {
            $this->insertTeaches($pSchool, $pSchool->getSubjects()[$i]);
        }
    }

    function insertSubject($pSubject) {
        // check parameter
        if (!$pSubject instanceof Subject) throw new Exception("subject needs to be a subject-object");

        // convert selections types (array) to sql style
        $strTags = "ARRAY[";
        for($i = 0; $i < count($pSubject->getTags()); $i++) {
            $strTags .= "'".$pSubject->getTags()[$i]."', ";
        }
        //replace "," with "]"
        $strTags = substr($strTags, 0, -2)."]";

        // if empty array
        if (count($pSubject->getTags()) == 0) $strTags = "ARRAY[]::varchar[]";

        $this->insertInto(StandardConnection::subjectTableName, new Entry("name", "'".$pSubject->getName()."'"), new Entry("tags", $strTags));
    }

    function insertStudent($pStudent, $pExamReg, $pConsTeacher) {
        // check parameter
        if (!$pStudent instanceof Student) throw new Exception("student needs to be a student-object");

        // convert selection (two dimensional array) to sql style
        $strSelection = "ARRAY[[";
        for($i = 0; $i < count($pStudent->getSelection()); $i++) {
            for($j = 0; $j < count($pStudent->getSelection()[$i]); $j++) {
                $strSelection .= $pStudent->getSelection()[$i][$j].", ";
            }
            // replace "," with "], [
            $strSelection = substr($strSelection, 0, -2)."], [";
            $strSelection = $strSelection;
        }
        // replace ", [" with "]"
        $strSelection = substr($strSelection, 0, -3)."]";

        // generate username
        $username = trim(strtolower($pStudent->getSurname()).strtolower(substr($pStudent->getFirstname(), 0, 1)), " ");

        // check if username is already used
        $rows = pg_fetch_all(pg_query($this->conn, "SELECT * FROM ".StandardConnection::studentTableName));
        $i = 0;
        while ($rows != null && $i < count($rows)) {
            if ($rows[$i]["nutzername"] == $username) {
                $i = 0;
                $username = trim(strtolower($pStudent->getSurname()).strtolower(substr($pStudent->getFirstname(), 0, strlen($username) - strlen($pStudent->getSurname()) + 1)), " ");
            } else {
                 $i += 1;
            }
        }

        $this->insertInto(StandardConnection::studentTableName, new Entry("nutzername", "'".$username."'"), new Entry("passwort", "'\$2a\$10\$M6ACIXoGnXvuel59Su4NMOR1SA8aLYhoot0aFQj5gFHKMwl9OQGTq'"), new Entry("vorname", "'".$pStudent->getFirstname()."'"), new Entry("nachname", "'".$pStudent->getSurname()."'"), new Entry("stufe", $this->findGrade($pStudent->getGrade(), $pStudent->getSchool(), $pExamReg, $pConsTeacher)), new Entry("wahl", $strSelection), new Entry("abifaecher", "ARRAY [-1, -1, -1, -1]"), new Entry("konfession", "'".$pStudent->getDenomination()."'"));
    }

    function insertTeacher($pTeacher, $pExamReg, $pConsTeacher) {
        // check parameter
        if (!$pTeacher instanceof Teacher) throw new Exception("teacher needs to be a teacher-object");

        // generate username
        $username = trim(strtolower($pTeacher->getName()), " ");

        $this->insertInto(StandardConnection::teacherTableName, new Entry("nutzername", "'".$username."'"), new Entry("passwort", "'\$2a\$10\$zuc2roApNkOwVbgYPcTNAeumA8uN.E4EmC4PF7YzvsShlHY/D3xtG'"), new Entry("name", "'".$pTeacher->getName()."'"), new Entry("stufe", $this->findGrade($pTeacher->getGrade(), $pTeacher->getSchool(), $pExamReg, $pConsTeacher)));
    }

    function insertGrade($pGrade, $pSchool, $pExamReg, $pConsTeacher) {
        // check parameter
        if (!is_string($pGrade)) throw new Exception("grade needs to be a string");
        if (!$pSchool instanceof School) throw new Exception("school needs to be a school-object");
        if (!is_string($pExamReg)) throw new Exception("examReg needs to be a string");
        if (!is_string($pConsTeacher)) throw new Exception("consTeacher needs to be a string");
        
        $this->insertInto(StandardConnection::gradeTableName, new Entry("bezeichnung", "'".$pGrade."'"), new Entry("schule", $this->findSchool($pSchool)), new Entry("pruefungsordnung", "'".$pExamReg."'"), new Entry("beratungslehrer", "'".$pConsTeacher."'"));
    }

    function insertTeaches($pSchool, $pSubject) {
        // check parameter
        if (!$pSchool instanceof School) throw new Exception("school needs to be a school-object");
        if (!$pSubject instanceof Subject) throw new Exception("subject needs to be a subject-object");

        if (pg_fetch_assoc(pg_query($this->conn, "SELECT * FROM ".StandardConnection::teachesTableName." WHERE schule = '".$this->findSchool($pSchool)."' AND fach = '". $this->findSubject($pSubject)."';")) != null) return;
        $this->insertInto(StandardConnection::teachesTableName, new Entry("schule", $this->findSchool($pSchool)), new Entry("fach", $this->findSubject($pSubject)));
    }

    function insertLanguage($pStudent, $pSubject, $pSelected, $pDeselected) {
        // check parameter
        if (!$pStudent instanceof Student) throw new Exception("student needs to be a student-object");
        if (!$pSubject instanceof Subject) throw new Exception("subject needs to be a subject-object");
        if (!is_numeric($pSelected)) throw new Exception("selected needs to be numeric");
        if (!is_numeric($pDeselected)) throw new Exception("deselected needs to be numeric");

        // find previous highest order (number)
        $order = (int) pg_fetch_assoc(pg_query($this->conn, "SELECT MAX(reihenfolge) FROM ".StandardConnection::languageTableName." WHERE schueler = '".$this->findStudent($pStudent)."';"))["max"];
        $this->insertInto(StandardConnection::languageTableName, new Entry("schueler", $this->findStudent($pStudent)), new Entry("reihenfolge", $order + 1), new Entry("fach", $this->findSubject($pSubject)), new Entry("angewaehlt", $pSelected), new Entry("abgewaehlt", $pDeselected));
    }

    //
    // methods to find a data set, returns the id/index | data set is created if it doesnt exists
    // used to create relations between tables
    //

    function findGrade($pGrade, $pSchool, $pExamReg, $pConsTeacher):int {
        $row = pg_fetch_assoc(pg_query($this->conn, "SELECT * FROM ".StandardConnection::gradeTableName." WHERE bezeichnung = '".$pGrade."' AND schule = ".$this->findSchool($pSchool).";"));

        // create grade if it doesnt exists
        if ($row == null) {
            $this->insertGrade($pGrade, $pSchool, $pExamReg, $pConsTeacher);
            return $this->findGrade($pGrade, $pSchool, $pExamReg, $pConsTeacher);
        }

        return $row["stufenid"];
    }

    function findSchool($pSchool):int {
        $row = pg_fetch_assoc(pg_query($this->conn, "SELECT * FROM ".StandardConnection::schoolTableName." WHERE name = '".$pSchool->getName()."';"));

        // create school if it doesnt exists
        if ($row == null) {
            $this->insertSchool($pSchool);
            return $this->findSchool($pSchool);
        }

        return $row["schulid"];
    }

    function findSubject($pSubject):int {
        $row = pg_fetch_assoc(pg_query($this->conn, "SELECT * FROM ".StandardConnection::subjectTableName." WHERE name = '".$pSubject->getName()."';"));

        // create grade if it doesnt exists
        if ($row == null) {
            $this->insertSubject($pSubject);
            return $this->findSubject($pSubject);
        }

        return $row["fachid"];
    }

    function findStudent($pStudent):int {
        $row = pg_fetch_assoc(pg_query($this->conn, "SELECT * FROM ".StandardConnection::studentTableName." JOIN ".StandardConnection::gradeTableName." ON ".StandardConnection::studentTableName.".stufe = ".StandardConnection::gradeTableName."
                                                    .stufenid WHERE vorname = '".$pStudent->getFirstname(). "' AND nachname = '" . $pStudent->getSurname() . "' AND ".StandardConnection::gradeTableName.".schule = ".$this->findSchool($pStudent->getSchool()).";"));
        
        // create student if it doesnt exists
        if ($row == null) {
            $this->insertStudent($pStudent, "", "");
            return $this->findStudent($pStudent);
        }

        return $row["schuelerid"];
    }

    function findTeacher($pTeacher):int {
        $row = pg_fetch_assoc(pg_query($this->conn, "SELECT * FROM ".StandardConnection::teacherTableName." JOIN ".StandardConnection::gradeTableName." ON ".StandardConnection::teacherTableName.".stufe = ".StandardConnection::gradeTableName."
                                                    .stufenid WHERE name = '".$pTeacher->getName(). "' AND ".StandardConnection::gradeTableName.".schule = ".$this->findSchool($pTeacher->getSchool()).";"));
        

        return $row["lehrerid"];
    }

    //
    // get php objects from tables
    //

    function getSchool($pId):School|null {
        $row = pg_fetch_assoc(pg_query($this->conn, "SELECT * FROM ".StandardConnection::schoolTableName." WHERE schulid = ".$pId.";"));

        // return null if no entry
        if ($row == null) return null;

        // convert selection types to php array
        $selArray = explode('","', subStr($row["wahltypen"], 2, -2));
        for ($i = 0; $i < count($selArray); $i++) {
            $selectionTypes[$i] = SelectionType::toSelectionType($selArray[$i]);
        }
        
        return new School($row["name"], $this->getSubjects($pId), $selectionTypes);
    }

    function getStudent($pUsername, $pSchoolId):Student|null {
        $row = pg_fetch_assoc(pg_query($this->conn, "SELECT * FROM ".StandardConnection::studentTableName." JOIN ".StandardConnection::gradeTableName." ON ".StandardConnection::studentTableName.".stufe = ".StandardConnection::gradeTableName."
                                                    .stufenid WHERE nutzername = '".$pUsername."' AND ".StandardConnection::gradeTableName.".schule = ".$pSchoolId.";"));

        // return null if no entry
        if ($row == null) return null;

        // extract selection array
        $selectionArr = explode("},{", substr($row["wahl"], 2, -2));
        for ($i = 0; $i < count($selectionArr); $i++) {
            $selection[$i] = explode(",", $selectionArr[$i]);
        }

        return new Student($row["vorname"], $row["nachname"], $row["bezeichnung"], $this->getSchool($row["schule"]), $selection, $row["konfession"], $row["pruefungsordnung"]);
    }

    function getTeacher($pUsername, $pSchoolId):Teacher|null {
        $row = pg_fetch_assoc(pg_query($this->conn, "SELECT * FROM ".StandardConnection::teacherTableName." JOIN ".StandardConnection::gradeTableName." ON ".StandardConnection::teacherTableName.".stufe = ".StandardConnection::gradeTableName."
                                                    .stufenid WHERE nutzername = '".$pUsername."' AND ".StandardConnection::gradeTableName.".schule = ".$pSchoolId.";"));

        // return null if no entry
        if ($row == null) return null;


        return new Teacher($row["name"], $row["bezeichnung"], $this->getSchool($row["schule"]));
    }

    function getStudentByToken($pToken):Student|null {
        $row = pg_fetch_assoc(pg_query($this->conn, "SELECT * FROM ".StandardConnection::usertokenTableName." JOIN ".StandardConnection::studentTableName." ON ".StandardConnection::studentTableName.".schuelerId = ".StandardConnection::usertokenTableName
                                                    .".schueler JOIN ".StandardConnection::gradeTableName." ON ".StandardConnection::studentTableName.".stufe = ".StandardConnection::gradeTableName.".stufenid WHERE token = '".$pToken."';"));

        return $this->getStudent($row["nutzername"], $row["schule"]);
    }

    function getTeacherByToken($pToken):Teacher|null {
        $row = pg_fetch_assoc(pg_query($this->conn, "SELECT * FROM ".StandardConnection::usertokenTableName." JOIN ".StandardConnection::teacherTableName." ON ".StandardConnection::teacherTableName.".lehrerId = ".StandardConnection::usertokenTableName
                                                    .".lehrer JOIN ".StandardConnection::gradeTableName." ON ".StandardConnection::teacherTableName.".stufe = ".StandardConnection::gradeTableName.".stufenid WHERE token = '".$pToken."';"));

        return $this->getTeacher($row["nutzername"], $row["schule"]);
    }

    //returns array of students and their usernames
    function getAssociatedStudents($pToken) {
        $rows = pg_fetch_all(pg_query($this->conn, "SELECT * FROM ".StandardConnection::usertokenTableName." JOIN ".StandardConnection::teacherTableName." ON ".StandardConnection::teacherTableName.".lehrerId = ".StandardConnection::usertokenTableName
                                                    .".lehrer JOIN ".StandardConnection::gradeTableName." ON ".StandardConnection::teacherTableName.".stufe = ".StandardConnection::gradeTableName.".stufenid JOIN ".StandardConnection::studentTableName." ON ".StandardConnection::studentTableName.".stufe = ".StandardConnection::gradeTableName.".stufenid WHERE token = '".$pToken."';"));
        $associatedStudents = array();
        $associatedUsernames = array();
        foreach($rows as $row) {
            array_push($associatedStudents, $this->getStudent($row['nutzername'],$row['schule']));
            array_push($associatedUsernames, $row['nutzername']);
        }
        $result = array("students" => $associatedStudents, "usernames" => $associatedUsernames);
        return $result;
    }

    function getAssociatedStudent($pToken, $pUsername) {
        $row = pg_fetch_assoc(pg_query($this->conn, "SELECT * FROM ".StandardConnection::usertokenTableName." JOIN ".StandardConnection::teacherTableName." ON ".StandardConnection::teacherTableName.".lehrerId = ".StandardConnection::usertokenTableName
                                                    .".lehrer JOIN ".StandardConnection::gradeTableName." ON ".StandardConnection::teacherTableName.".stufe = ".StandardConnection::gradeTableName.".stufenid JOIN ".StandardConnection::studentTableName." ON ".StandardConnection::studentTableName.".stufe = ".StandardConnection::gradeTableName.".stufenid WHERE token = '".$pToken."' AND ".StandardConnection::studentTableName.".nutzername ='".$pUsername."';"));

        return $this->getStudent($row['nutzername'],$row['schule']);
    }

    //get certain data values

    function getUsernameByToken($pToken) {
        $row = pg_fetch_assoc(pg_query($this->conn, "SELECT * FROM ".StandardConnection::usertokenTableName." JOIN ".StandardConnection::studentTableName." ON ".StandardConnection::studentTableName.".schuelerId = ".StandardConnection::usertokenTableName
                                                    .".schueler JOIN ".StandardConnection::gradeTableName." ON ".StandardConnection::studentTableName.".stufe = ".StandardConnection::gradeTableName.".stufenid WHERE token = '".$pToken."';"));
        return $row["nutzername"];
    }

    function getExamReg($pStudent) {
        $id = $this->findStudent($pStudent);
        $row = pg_fetch_assoc(pg_query($this->conn, "SELECT * FROM ".StandardConnection::studentTableName." JOIN ".StandardConnection::gradeTableName." ON ".StandardConnection::gradeTableName.".stufenid = ".StandardConnection::studentTableName
                                                    .".stufe WHERE schuelerid = ".$id.";"));
        return $row['pruefungsordnung'];
    }

    function getSupportTeacher($pStudent) {
        $id = $this->findStudent($pStudent);
        $row = pg_fetch_assoc(pg_query($this->conn, "SELECT * FROM ".StandardConnection::studentTableName." JOIN ".StandardConnection::gradeTableName." ON ".StandardConnection::gradeTableName.".stufenid = ".StandardConnection::studentTableName
                                                    .".stufe WHERE schuelerid = ".$id.";"));
        return $row['beratungslehrer'];
    }

    function getSubjects($pSchoolid):Array|null {
        $rows = pg_fetch_all(pg_query($this->conn, "SELECT * FROM ".StandardConnection::teachesTableName." JOIN ".StandardConnection::subjectTableName." ON ".StandardConnection::subjectTableName.".fachid = ".StandardConnection::teachesTableName."
                                                    .fach WHERE schule = ".$pSchoolid.";"));

        // return null if no entry
        if ($rows == null) return null;

        for ($i = 0; $i < count($rows); $i++) {
            if ($rows[$i]["tags"] == "{}") $subjects[$i] = new Subject($rows[$i]["name"], array());
            else $subjects[$i] = new Subject($rows[$i]["name"], explode(',', subStr($rows[$i]["tags"], 1, -1)));
        }

        return $subjects;
    }

    function getAbifaecher($pStudent) {
        $id = $this->findStudent($pStudent);
        $row = pg_fetch_assoc(pg_query($this->conn, "SELECT * FROM ".StandardConnection::studentTableName." WHERE schuelerid = ".$id.";"));

        return $row['abifaecher'];
    }

    //
    // methods to update rows in tables
    //

    function updateStudent($pUsername, ...$entry) {
        $entryUsername = "'".$pUsername."'";
        $this->updateRow("schueler", "nutzername", $entryUsername, ...$entry);
    }

    function updateStudentSelection($pUsername, $selection, $abi) {
        // convert selection (two dimensional array) to sql style
        $strSelection = "ARRAY[[";
        for($i = 0; $i < count($selection); $i++) {
            for($j = 0; $j < count($selection[$i]); $j++) {
                $strSelection .= $selection[$i][$j].", ";
            }
            // replace ", " with "], ["
            $strSelection = substr($strSelection, 0, -2)."], [";
            $strSelection = $strSelection;
        }
        // replace ", [" with "]"
        $strSelection = substr($strSelection, 0, -3)."]";

        // convert abi (array) to sql style
        $strAbi = "ARRAY[";
        for($i = 0; $i < count($abi); $i++) {
            $strAbi .= $abi[$i].", ";
        }
        //replace "," with "]"
        $strAbi = substr($strAbi, 0, -2)."]";

        //update student
        $this->updateStudent($pUsername, new Entry("wahl", $strSelection), new Entry("abifaecher", $strAbi));
    }

    //
    // methods to control token/password generation/deletion/testing
    //

    // Checks if user listed with value "value" in "idcolumn" has value "password" in "passColumn"
    function checkPassword($pSchoolId, $pUsername, $pPassword):bool {
        $row = pg_fetch_assoc(pg_query($this->conn, "SELECT * FROM ".StandardConnection::studentTableName." JOIN ".StandardConnection::gradeTableName." ON ".StandardConnection::studentTableName.".stufe = ".StandardConnection::gradeTableName."
                                                    .stufenid WHERE nutzername = '".$pUsername."' AND ".StandardConnection::gradeTableName.".schule = ".$pSchoolId.";"));
        return $row != null && password_verify($pPassword, $row["passwort"]);
    }

    // Checks if user listed with value "value" in "idcolumn" has value "password" in "passColumn"
    function checkTeacherPassword($pSchoolId, $pUsername, $pPassword):bool {
        $row = pg_fetch_assoc(pg_query($this->conn, "SELECT * FROM ".StandardConnection::teacherTableName." JOIN ".StandardConnection::gradeTableName." ON ".StandardConnection::teacherTableName.".stufe = ".StandardConnection::gradeTableName."
                                                    .stufenid WHERE nutzername = '".$pUsername."' AND ".StandardConnection::gradeTableName.".schule = ".$pSchoolId.";"));
        return $row != null && password_verify($pPassword, $row["passwort"]);
    }

    // Generates a new token, which is valid for the next 24 hours
    function generateToken($pStudent):String {
        // find unused token https://stackoverflow.com/a/13212994
        $length = 12;
        $token = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);

        // check if token is already used
        $rows = pg_fetch_all(pg_query($this->conn, "SELECT * FROM ".StandardConnection::usertokenTableName));
        $i = 0;
        while ($rows != null && $i < count($rows)) {
            if ($rows[$i]["token"] == $token) {
                $i = 0;
                $token = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
            } else {
                $i += 1;
            }
        }

        // insert into database
        $this->insertInto(StandardConnection::usertokenTableName, new Entry("token", "'".$token."'"), new Entry("schueler", $this->findStudent($pStudent)), new Entry("lehrer", "1"), new Entry("gueltigBis", "now() + (INTERVAL '1 hour' * 12)"));

        return $token;
    }

    // Generates a new token, which is valid for the next 24 hours
    function generateTeacherToken($pTeacher):String {
        // find unused token https://stackoverflow.com/a/13212994
        $length = 12;
        $token = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);

        // check if token is already used
        $rows = pg_fetch_all(pg_query($this->conn, "SELECT * FROM ".StandardConnection::usertokenTableName));
        $i = 0;
        while ($rows != null && $i < count($rows)) {
            if ($rows[$i]["token"] == $token) {
                $i = 0;
                $token = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
            } else {
                $i += 1;
            }
        }

        // insert into database
        $this->insertInto(StandardConnection::usertokenTableName, new Entry("token", "'".$token."'"), new Entry("schueler", "1"), new Entry("lehrer", $this->findTeacher($pTeacher)), new Entry("gueltigBis", "now() + (INTERVAL '1 hour' * 12)"));

        return $token;
    }

    //returns weather a token is associated with a teacher
    function isTeacher($pToken):bool {
        $row = pg_fetch_assoc(pg_query($this->conn, "SELECT * FROM ".StandardConnection::usertokenTableName." JOIN ".StandardConnection::teacherTableName." ON ".StandardConnection::teacherTableName.".lehrerId = ".StandardConnection::usertokenTableName.".lehrer WHERE token = '".$pToken."';"));
        return ($row['name'] != "Kein Lehrer");
    }

    function checkToken($pToken):bool {
        // remove all old tokens from table
        $this->removeOldTokens();

        // check if token is in table
        $rows = pg_fetch_all(pg_query($this->conn, "SELECT * FROM ".StandardConnection::usertokenTableName));
        $i = 0;
        while ($rows != null && $i < count($rows)) {
            if ($rows[$i]["token"] == $pToken) {
                return true;
            } else { 
                $i += 1;
            }
        }
        return false;
    }

    function removeOldTokens() {
        $rows = pg_fetch_all(pg_query($this->conn, "SELECT * FROM ".StandardConnection::usertokenTableName));
        for ($i = 0; $i < count($rows); $i++) {
            if (strtotime($rows[$i]["gueltigbis"]) < time()) pg_query($this->conn, "DELETE FROM ".StandardConnection::usertokenTableName." WHERE token = '".$rows[$i]["token"]."';");
        }
    }

    function removeToken($pToken) {
        pg_query($this->conn, "DELETE FROM ".StandardConnection::usertokenTableName." WHERE token = '".$pToken."';");
    }
}

//Default Class to define Columns of Tables
class TableColumn {
    public $name;
    public $type;
    public $constraint;

    //Constraint constants
    const Primary_Key = "PRIMARY KEY";

    public function __construct($cName, $cType, $cConstraint = null) {
        $this->name = $cName;
        $this->type = $cType;
        $this->constraint = $cConstraint;
    }
    public static function references($table, $column) {
        $referenceString = "REFERENCES ".$table."(".$column.")";
        return $referenceString;
    }
}

//Default class for Row entries
class Entry {
    public $column;
    public $value;

    public function __construct($pColumn, $pValue) {
        $this->column = $pColumn;
        $this->value = $pValue;
    }
}
?>
