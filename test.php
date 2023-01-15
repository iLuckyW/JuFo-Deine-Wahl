<?php

require_once("modules/dataClasses/School.php");
require_once("modules/dataClasses/Student.php");
require_once("modules/dataClasses/Teacher.php");
require_once("modules/dataClasses/SelectionType.php");
require_once("modules/dataClasses/Selection.php");
require_once("modules/postgre/StandardConnection.php");
require_once("modules/pdf/StudentPrintout.php");

$subjects = array(new Subject("Deutsch", array("slk", "lks", "pfl")), new Subject("Englisch", array("slk", "fsp", "lks")), new Subject("Französisch", array("slk", "fsp", "lks")), new Subject("Lateinisch", array("slk", "fsp")), new Subject("Spanisch", array("slk", "fsp", "lks")), new Subject("Chinesisch", array("slk","fsp", "nfs")),
                new Subject("Musik", array("slk")), new Subject("Kunst", array("slk", "lks")), new Subject("Literatur Medien", array("ltk","slk", "mdl")), new Subject("Literatur Schreiben", array("ltk","slk", "mdl")), new Subject("Literatur Theater", array("ltk","slk", "mdl")), new Subject("Chor", array("ltk","slk", "mdl")),  new Subject("Big Band", array("ltk","slk", "mdl")), new Subject("Rock Band", array("ltk","slk", "mdl")),
                new Subject("Geschichte", array("gws", "zsk", "lks")), new Subject("Geschichte bilingual", array("gws", "bil")), new Subject("Geographie", array("gws", "lks")), new Subject("Sozialwissenschaften", array("gws", "zsk", "lks")), new Subject("Sozialw.-Wirtschaft", array("gws", "zsk", "lks")), new Subject("Erziehungswis.", array("gws")),
                new Subject("Mathematik", array("mnt", "lks", "pfl")), new Subject("Physik", array("mnt", "lks")), new Subject("Chemie", array("mnt", "lks")), new Subject("Biologie", array("mnt", "lks")), new Subject("Biologie bilingual", array("mnt", "bil")), new Subject("Informatik", array("mnt", "lks")), 
                new Subject("Philosophie", array("gws", "rel")), new Subject("Religionslehre", array("rel")), new Subject("Sport", array("pfl", "mdl")), new Subject("Vertiefungsfach Deutsch", array("vtf", "mdl")), new Subject("Vertiefungsfach Mathematik", array("vtf", "mdl")), new Subject("Vertiefungsfach Englisch", array("vtf", "mdl")), new Subject("Vertiefungsfach Spanisch", array("vtf", "mdl")), new Subject("Vertiefungsfach Französisch", array("vtf", "mdl")),
                new Subject("Projektkurs Informatik", array("pjk", "mdl")), new Subject("Projektkurs MINT", array("pjk", "mdl")), new Subject("Projektkurs Geschichte", array("pjk", "mdl")), new Subject("Projektkurs FFP+", array("pjk", "mdl")), new Subject("Projektkurs Held ohne Geld", array("pjk", "mdl")), new Subject("Projektkurs Gesellschafts.", array("pjk", "mdl")));
                

$selectionTypes = array(new SelectionType("nicht gewaehlt", "-"), new SelectionType("muendlich", "M"), new SelectionType("schriftlich", "S"),
                    new SelectionType("Leistungskurs", "LK"), new SelectionType("Zusatzkurs", "ZK"));

// [Semester][Wahl (Index = Fach, Wert = Wahloption)]
$wrong_selection = array(array("2","2","0","0","0","2","0","2","0","0","0","0","0","0","2","0","0","0","0","0","2","2","2","2","0","2","2","0","1","0","0","0","0","0","0","0","0","0","0","0"), 
array("2","2","0","0","0","2","0","2","0","0","0","0","0","0","1","0","0","0","0","0","2","2","2","2","0","2","2","0","1","0","0","0","0","0","0","0","0","0","0","0"), 
array("3","2","0","0","0","2","0","0","0","0","0","1","0","0","2","0","0","0","0","0","3","0","1","0","0","2","2","0","1","0","0","0","0","0","0","0","0","0","0","0"), 
array("3","0","0","0","0","2","0","0","0","0","0","1","0","0","2","0","0","0","0","0","3","1","1","0","0","2","2","0","1","0","0","0","0","0","0","0","0","0","0","0"), 
array("3","2","0","0","0","2","0","0","0","0","0","0","0","0","2","0","0","4","0","0","3","1","1","0","0","0","2","0","1","0","0","0","0","0","0","0","0","0","0","0"), 
array("3","1","0","0","0","1","0","0","0","0","0","0","0","0","1","0","0","0","4","0","3","1","1","0","0","0","2","0","1","0","0","0","0","0","0","0","0","0","0","0"));
$emptySelection = array_fill(0, 6, array_fill(0, count($subjects), 0));


$school1 = new School("Annette-von-Droste-Hülshoff Gymnasium", $subjects, $selectionTypes);

//Example students
$student1 = new Student("Max", "Mustermann", "EF", $school1, $wrong_selection, "KR");
$student2 = new Student("Bernhard", "Diener", "EF", $school1, $emptySelection, "ER");
$student3 = new Student("Alexander", "Platz", "EF", $school1, $emptySelection, "KR");
$student4 = new Student("Ann", "Geber", "EF", $school1, $emptySelection, "ER");
$student5 = new Student("Anna", "Nass", "EF", $school1, $emptySelection, "KR");
$student6 = new Student("Claire", "Grube", "EF", $school1, $emptySelection, "KR");
$student7 = new Student("Ellen", "Lang", "EF", $school1, $emptySelection, "ER");
$student8 = new Student("Ernst", "Fall", "EF", $school1, $emptySelection, "KR");
$student9 = new Student("Hella", "Wahnsinn", "EF", $school1, $emptySelection, "KR");
$student10 = new Student("Kai", "Mauer", "EF", $school1, $emptySelection, "ER");
$student11 = new Student("Karl", "Ender", "EF", $school1, $emptySelection, "ER");
$student12 = new Student("Klara", "Fall", "EF", $school1, $emptySelection, "KR");
$student13 = new Student("Klaus", "Throphobie", "EF", $school1, $emptySelection, "ER");
$student14 = new Student("Maria", "Kron", "EF", $school1, $emptySelection, "KR");
$student15 = new Student("Mark", "Aber", "EF", $school1, $emptySelection, "KR");
$student16 = new Student("Otto", "Päde", "EF", $school1, $emptySelection, "ER");
$student17 = new Student("Paul", "Lahner", "EF", $school1, $emptySelection, "ER");
$student18 = new Student("Peter", "Silie", "EF", $school1, $emptySelection, "KR");
$student19 = new Student("Rainer", "Zufall", "EF", $school1, $emptySelection, "KR");
$student20 = new Student("Roy", "Bär", "EF", $school1, $emptySelection, "ER");
$student21 = new Student("Sunny", "Teter", "EF", $school1, $emptySelection, "ER");
$student22 = new Student("Tim", "Buktu", "EF", $school1, $emptySelection, "KR");
$student23 = new Student("Polly", "Zist", "EF", $school1, $emptySelection, "KR");
$student24 = new Student("Bill", "Yard", "EF", $school1, $emptySelection, "ER");
$student25 = new Student("Jim", "Panse", "EF", $school1, $emptySelection, "ER");
$student26 = new Student("Lars", "Christmas", "EF", $school1, $emptySelection, "KR");

$teacher1 = new Teacher("Grote", "EF", $school1);

//createConnection and setup Database
$conn = new StandardConnection();
$conn->setupDatabase();

//insert dummy school
$dummySchool = new School("Keine Schule gefunden", array(new Subject("Fach nicht gefunden", array("lks"))), array(new SelectionType("nicht gewaehlt", "-")));
$conn->insertSchool($dummySchool);
//insert dummy pupil reference for teacher accounts and dummy teacher reference for pupil accounts
$conn->insertStudent(new Student("Kein", "Schüler", "EF", $dummySchool, array(array("0"), array(0), array("0"), array(0), array("0"), array(0)), "KR"), "Kein Beratungslehrer gefunden", "APO-GOSt");
$conn->insertTeacher(new Teacher("Kein Lehrer", "EF", $dummySchool), "Kein Beratungslehrer gefunden", "APO-GOSt");


//insert test objects
$conn->insertSchool($school1);
$conn->insertStudent($student1, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student2, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student3, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student4, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student5, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student6, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student7, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student8, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student9, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student10, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student11, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student12, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student13, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student14, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student15, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student16, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student17, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student18, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student19, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student20, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student21, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student22, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student23, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student24, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student25, "APO-GOSt(B)10/G8", "Grote");
$conn->insertStudent($student26, "APO-GOSt(B)10/G8", "Grote");

$conn->insertTeacher($teacher1, "APO-GOSt(B)10/G8", "Grote");
$conn->insertLanguage($student1, $subjects[1], 5.1, 0);
$conn->insertLanguage($student1, $subjects[2], 7.1, 9.4);

$DBstudent = $conn->getStudent("mustermannm", 2);
echo $DBstudent->getFirstname()." ".$DBstudent->getSurname()." ".$DBstudent->getGrade()." ".$DBstudent->getSchool()->getName()."<br />";
if ($conn->checkPassword(2, "mustermannm", "An-nette")) echo "passwort richtig"."<br />";
else echo "passwort falsch"."<br />";

?>
