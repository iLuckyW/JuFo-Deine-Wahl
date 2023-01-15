<?php
//load classes
require_once "modules/dataClasses/Student.php";
require_once "modules/dataClasses/School.php";
require_once "modules/dataClasses/Subject.php";
require_once "libaries/fpdf.php";

//the Fpdf object
$pdf;
//the number of rows
$rows;

class StudentPrintout {
    public function __construct() {
        $this->pdf = new FPDF('P','mm','A4');
        $this->pdf->AddPage();
    }

    public function configureStandard() {
        $this->pdf->SetFont('Arial','',10);
        $this->pdf->SetMargins(1, 0.6, 1);
    }

    public function printHead($schule=null, $name=null, $vorname= null, $nextSemester=null, $currentClass=null, $examReg=null) {
        //fix special characters
        $schule = iconv('UTF-8', 'windows-1252', $schule);
        $name = iconv('UTF-8', 'windows-1252', $name);
        $vorname = iconv('UTF-8', 'windows-1252', $vorname);
        $nextSemester = iconv('UTF-8', 'windows-1252', $nextSemester);
        $currentClass = iconv('UTF-8', 'windows-1252', $currentClass);
        //first line
        $this->pdf->SetXY(16, 15);
        $this->pdf->Cell(0,0,$examReg,0,0,'L');
        $this->pdf->SetFont('Arial','B',14);
        //calculate postion
        $w = 110;
        $this->pdf->SetXY((210-$w)/2, 12);
        $this->pdf->MultiCell($w,7,$this->addLineBreaks($schule, $w),0,'C');
        $this->pdf->SetFont('Arial','B',12);
        $this->pdf->SetXY((210-20), 15);
        $this->pdf->Cell(0,0,$currentClass,0,0,'L');
        $this->pdf->SetXY(16, 37);
        //sceond and third line
        $this->pdf->Cell(0,0,iconv('UTF-8', 'windows-1252', "Wahlbogen für das Halbjahr ").$nextSemester." von ",0,0,'L');
        $this->pdf->SetXY(95, 37);
        $this->pdf->SetFont('Arial','B',15);
        $this->pdf->Cell(0,0,$name.", ".$vorname,0,0,'L');
        $this->pdf->SetFont('Arial','',10);
        $this->pdf->SetXY(16, 43);
        $this->pdf->Cell(0,0,iconv('UTF-8', 'windows-1252', "Hiermit wähle ich verbindlich für das Schulhalbjahr ").$nextSemester.iconv('UTF-8', 'windows-1252'," die folgenden Fächer:"),0,0,'L');
    }

    //function to print Table $faecher has to be an Array of subject names $wahl has to be an two-dimensional array of SelectionLetters (e.g. "M") $abi has to be 
    public function printTable($faecher, $wahl, $abi) {
        //change abi from string to int
        for($i = 0; $i < count($abi); $i++) {
            $abi[$i] = (int) $abi[$i];
        }
        //delete never chosen subjects
        for($i = 0; $i < count($wahl[0]); $i++) {
            $choosen = 0;
            for($j = 0; $j < 6; $j++) {
                if($wahl[$j][$i] != "-") {
                    $choosen ++;
                }
            }
            if($choosen == 0) {
                for($j = 0; $j < 6; $j++) {
                    array_splice($wahl[$j], $i, 1);
                }
                array_splice($faecher, $i, 1);
                //lower abi nums
                for($k = 0; $k < count($abi); $k++) {
                    if($i < $abi[$k]) $abi[$k]--;
                }
                //reduce i to not skip following element
                $i--;
            }
        }
        //Print table Header
        $this->pdf->SetFont('Arial','B',10);
        $this->pdf->SetXY(74, 48);
        $this->pdf->Cell(0,0,"Sprachenfolge",0,0,'L');
        $this->pdf->SetXY(16, 52);
        $this->pdf->Cell(0,0,"Fach",0,0,'L');
        $this->pdf->SetXY(71, 52);
        $this->pdf->Cell(0,0,"Jahrg.",0,0,'L');
        $this->pdf->SetXY(86, 52);
        $this->pdf->Cell(0,0,"Reihenf.",0,0,'L');
        $this->pdf->SetXY(105, 52);
        $this->pdf->Cell(0,0,"EF.1",0,0,'L');
        $this->pdf->SetXY(120, 52);
        $this->pdf->Cell(0,0,"EF.2",0,0,'L');
        $this->pdf->SetXY(135, 52);
        $this->pdf->Cell(0,0,"Q1.1",0,0,'L');
        $this->pdf->SetXY(150, 52);
        $this->pdf->Cell(0,0,"Q1.2",0,0,'L');
        $this->pdf->SetXY(165, 52);
        $this->pdf->Cell(0,0,"Q2.1",0,0,'L');
        $this->pdf->SetXY(180, 52);
        $this->pdf->Cell(0,0,"Q2.2",0,0,'L');
        $this->pdf->SetXY(192, 52);
        $this->pdf->Cell(0,0,"AF",0,0,'L');
        //print Table
        $columns = 10;
        global $rows;
        $rows = count($faecher);
        $col = 0;
        $row = 0;
        //setup
        $this->pdf->SetFont('Arial','',10);
        $this->pdf->SetXY(16, 55.5);
        //print as many cells as columns*rows
        for($i = 0; $i < $columns*$rows; $i++) {
            //change cell width depending on column
            switch ($col) {
                //Fach
                case 0:
                    $this->pdf->SetX(16);
                    $this->pdf->Cell(55,4,$faecher[$row],1,0,'L');
                    break;
                //Sprachenfolge
                case 1:
                case 2:
                    $this->pdf->Cell(15,4,"",1,0,'L');
                    break;
                //Wahl
                case 3:
                case 4:
                case 5:
                case 6:
                case 7:
                case 8:
                    $this->pdf->Cell(15,4,$wahl[($col-3)][$row],1,0,'L');
                    break;
                //Abiturfach
                case 9:
                    $abiNum = array_search($row, $abi);
                    if($abiNum === false) {
                        $this->pdf->Cell(7,4,"",1,0,'L');
                    }
                    else {
                        $this->pdf->Cell(7,4,$this->getAbiText($abiNum),1,0,'L');
                    }
                default:
            }

            //Move to next column
            if($col < 9) {
                $col = ($col + 1);
            }
            //Move to next row
            else {
                $col = 0;
                $this->pdf->SetY($this->pdf->GetY()+4);
                $row = $row+1;
            }
        }
        //calculate course sum and semester hours
        $coursesInSemester = array(0,0,0,0,0,0);
        $semesterHours = array(0,0,0,0,0,0);
        for($i = 0; $i < 6; $i++) {
            for($j = 0; $j<$rows; $j++){
                if($wahl[$i][$j]!="-"){
                    $coursesInSemester[$i]++;
                }
                //calc additional lessons for advanced class
                if ($wahl[$i][$j]=="LK") {
                    $semesterHours[$i] = $semesterHours[$i] + 2;
                }
            }
            //calculate regular lesson sum
            $semesterHours[$i] = $semesterHours[$i] + 3*($coursesInSemester[$i]);
        }
        $inQPhase = $coursesInSemester[2]+$coursesInSemester[3]+$coursesInSemester[4]+$coursesInSemester[5];
        //Print course Sum
        $this->pdf->SetFont('Arial','B',10);
        $this->pdf->SetXY(81, 55.5+($rows*4)+5);
        $this->pdf->Cell(0,0,"Kurse",0,0,'L');
        $this->pdf->SetFont('Arial','',10);
        $this->pdf->SetXY(105, 55.5+($rows*4)+5);
        $this->pdf->Cell(0,0,$coursesInSemester[0],0,0,'L');
        $this->pdf->SetXY(120, 55.5+($rows*4)+5);
        $this->pdf->Cell(0,0,$coursesInSemester[1],0,0,'L');
        $this->pdf->SetXY(135, 55.5+($rows*4)+5);
        $this->pdf->Cell(0,0,$coursesInSemester[2],0,0,'L');
        $this->pdf->SetXY(150, 55.5+($rows*4)+5);
        $this->pdf->Cell(0,0,$coursesInSemester[3],0,0,'L');
        $this->pdf->SetXY(165, 55.5+($rows*4)+5);
        $this->pdf->Cell(0,0,$coursesInSemester[4],0,0,'L');
        $this->pdf->SetXY(180, 55.5+($rows*4)+5);
        $this->pdf->Cell(0,0,$coursesInSemester[5],0,0,'L');
        $this->pdf->SetFont('Arial','B',10);
        $this->pdf->SetXY(193, 55.5+($rows*4)+5);
        $this->pdf->Cell(5,0,$inQPhase,0,0,'R');
        //Add anotation
        $this->pdf->SetFont('Arial','',6);
        $this->pdf->SetXY(196, 55.5+($rows*4)+3);
        $this->pdf->Cell(0,0,"1)",0,0,'L');
        //Print semester hours sum
        //calculate overall
        $inQPhase = round(($semesterHours[0]+$semesterHours[1]+$semesterHours[2]+$semesterHours[3]+$semesterHours[4]+$semesterHours[5])/2);
        $this->pdf->SetFont('Arial','B',10);
        $this->pdf->SetXY(81, 66+($rows*4));
        $this->pdf->Cell(0,0,"Wochenstd.",0,0,'L');
        $this->pdf->SetFont('Arial','',10);
        $this->pdf->SetXY(105, 66+($rows*4));
        $this->pdf->Cell(0,0,$semesterHours[0],0,0,'L');
        $this->pdf->SetXY(120, 66+($rows*4));
        $this->pdf->Cell(0,0,$semesterHours[1],0,0,'L');
        $this->pdf->SetXY(135, 66+($rows*4));
        $this->pdf->Cell(0,0,$semesterHours[2],0,0,'L');
        $this->pdf->SetXY(150, 66+($rows*4));
        $this->pdf->Cell(0,0,$semesterHours[3],0,0,'L');
        $this->pdf->SetXY(165, 66+($rows*4));
        $this->pdf->Cell(0,0,$semesterHours[4],0,0,'L');
        $this->pdf->SetXY(180, 66+($rows*4));
        $this->pdf->Cell(0,0,$semesterHours[5],0,0,'L');
        $this->pdf->SetFont('Arial','B',10);
        $this->pdf->SetXY(193, 66+($rows*4));
        $this->pdf->Cell(5,0,$inQPhase,0,0,'R');
        //Add anotation
        $this->pdf->SetFont('Arial','',6);
        $this->pdf->SetXY(196, 64+($rows*4));
        $this->pdf->Cell(0,0,"2)",0,0,'L');
    }

    public function printNotes() {
        //get rows
        global $rows;
        $this->pdf->SetFont('Arial','',10);
        $this->pdf->SetXY(16, 73+($rows*4));
        $this->pdf->Cell(0,0,"Bermerkungen der Schule",0,0,'L');
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->SetXY(16, 77+($rows*4));
        $this->pdf->Cell(0,0,"-keine-",0,0,'L');
        $this->pdf->SetFont('Arial','',10);
        $this->pdf->SetXY(16, 83+($rows*4));
        $this->pdf->Cell(0,0,"Sonstige Hinweise zur Gesamtlaufbahn",0,0,'L');
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->SetXY(16, 87+($rows*4));
        $this->pdf->Cell(0,0,iconv('UTF-8', 'windows-1252', "Die Stundenbandbreite sollte pro Halbjahr 32 bis 36 Stunden betragen, um eine gleichmäßige Stundenbelastung zu gewährleisten"),0,0,'L');
    }

    public function printSignatureLines($supportTeacher) {
        //get rows
        global $rows;
        //Print lines
        $this->pdf->Line(16,116+($rows*4),106,116+($rows*4));
        $this->pdf->Line(16,131+($rows*4),106,131+($rows*4));
        $this->pdf->Line(113,131+($rows*4),199,131+($rows*4));
        //Print text
        $this->pdf->SetFont('Arial','',10);
        $this->pdf->SetXY(36, 120+($rows*4));
        $this->pdf->Cell(0,0,"Beratungslehrer: ".$supportTeacher,0,0,'L');
        $this->pdf->SetXY(113, 120+($rows*4));
        $this->pdf->Cell(0,0,"Beraten am: ",0,0,'L');
        $this->pdf->SetXY(16, 135+($rows*4));
        $this->pdf->Cell(90,0,iconv('UTF-8', 'windows-1252',"Unterschrift des Schülers"),0,0,'C');
        $this->pdf->SetXY(113, 135+($rows*4));
        $this->pdf->Cell(86,0,iconv('UTF-8', 'windows-1252',"Unterschrift eines Erziehunsberichtigten"),0,0,'C');
    }

    public function printFoot() {
        //draw line
        $this->pdf->Line(17, (297-15), 210-10, (297-15));
        //legal disclaimer
        $this->pdf->SetAutoPageBreak(true , 3);
        $this->pdf->SetFont('Arial','',7);
        $this->pdf->SetXY(17, 283.5);
        $this->pdf->MultiCell(210-27,1.5,iconv('UTF-8', 'windows-1252', "Schulorganisatorische Gründe können zu einer Änderung der Fachwahl und der Laufbahn führen. Korrekturwünsche können vor jedem Halbjahreswechsel nach\n
Rücksprache mit den Beratungslehrern durchgeführt werden"),0,'L');
        //annotations
        $this->pdf->SetXY(17, 291);
        $this->pdf->MultiCell(210-27,1.5,iconv('UTF-8', 'windows-1252', "1) Anzahl der anrechenbaren Kurse aus der Qualifikationsphase 2) Summe der durchschnittlichen Jahresstunden"),0,'L');
    }

    public function showPdf() {
        ob_start();
        $this->pdf->Output();
        ob_end_flush();
    }

    public function fullPrintOut($student, $examReg, $supportTeacher, $abi) {
        //check for correct type
        if (!$student instanceof Student)  throw new Exception("school needs to be an school-object");
        $this->configureStandard();
        $this->printHead($student->getSchool()->getName(), $student->getSurname(), $student->getFirstname(), $this->getNextGrade($student->getIntGrade()), $this->getNextGrade($student->getIntGrade()-1), $examReg);
        $this->printTable($student->getSchool()->getSubjectNames(), $student->getSelectionTypes(), $abi);
        $this->printNotes();
        $this->printSignatureLines($supportTeacher);
        $this->printFoot();
        $this->showPdf();
    }

    //returns next grade
    private function getNextGrade($grade) {
        if($grade < 9) return $grade+1;
        elseif($grade == 9) return "EF";
        elseif($grade == 10) return "Q1";
        else return "Q2";
    }

    //function to add Line Braks to Strings wich might exceed the width of a cell
    private function addLineBreaks($string, $maxWidth) {
        $buffer = "";
        $result = "";
        $new = "";
        while (strlen($string)>0) {
            $curChar = substr($string,0,1);
            if($curChar == " ") {
                $buffer = $buffer.$new;
                $new = " ";
                $string = substr($string,1);
            }
            elseif($curChar == "-") {
                $buffer = $buffer.$new."-";
                $new = "";
                $string = substr($string,1);
            }
            else
            {
            $new = $new.$curChar;
            $string = substr($string,1);
            }
            //check if string got to long:
             if($this->pdf->GetStringWidth($buffer.$new)>$maxWidth) {
                 $result = $result.$buffer."\n";
                 $buffer = "";
                if(substr($new,0,1)==" ") {
                      $new = substr($new,1);
                 }
            }
        }
        //move rest to result
        $result = $result.$buffer.$new;
        return $result;
    }

    private function getAbiText($num) {
        if($num == 0) {
            return "1";
        }
        else if($num == 1){
            return "2";
        }
        else if($num == 2){
            return "3";
        }
        else if($num == 3){
            return "4";
        }
    }
}