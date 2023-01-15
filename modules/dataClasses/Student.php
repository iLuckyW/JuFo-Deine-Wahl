<?php

class Student
{
    /* students firstname */
    private $firstname;
    
    /* students surname */
    private $surname;
    
    /* students grade
     *  - EF
     *  - Q1
     *  - Q2
     */
    private $grade;
    
    /* students school */
    private $school;

    /* students selection */
    private $selection;

    /* students denomination */
    private $denomination;
	
    public function __construct($pFirstname, $pSurname, $pGrade, $pSchool, $pSelection, $pDenomination)
    {
        $this->setFirstname($pFirstname);
        $this->setSurname($pSurname);
        $this->setGrade($pGrade);
        $this->setSchool($pSchool);
        $this->setSelection($pSelection);
        $this->setDenomination($pDenomination);
    }
    
    /* firstname getter and setter */
    public function getFirstname():String
    {
        return $this->firstname;
    }
    
    public function setFirstname($pFirstname)
    {
        $this->firstname = trim($pFirstname);
    }
    
    /* surname getter and setter */
    public function getSurname():String
    {
        return $this->surname;
    }
    
    private function setSurname($pSurname)
    {
        $this->surname = trim($pSurname);
    }
    
    /* grade getter and setter */
    public function getGrade():String
    {
        return $this->grade;
    }

    public function getIntGrade():int
    {
        if ($this->grade = "EF") return 10;
        if ($this->grade = "Q1") return 11;
        if ($this->grade = "Q2") return 12;
        return 9;
    }
    
    public function setGrade($pGrade)
    {
        $this->grade = $pGrade;
    }
    
    /* school getter and setter */
    public function getSchool():School
    {
        return $this->school;
    }
    
    private function setSchool($pSchool)
    {
        // null is a possible value
		if ($pSchool != null && !$pSchool instanceof School) throw new Exception("school needs to be a school-object");
		
		// set school
        $this->school = $pSchool;
    }

    /* selection getter and setter */
    public function getSelection():Array
    {
        return $this->selection;
    }
    
    public function setSelection($pSelection)
    {
        $this->selection = $pSelection;
    }

    /* denomination getter and setter */
    public function getDenomination():String
    {
        return $this->denomination;
    }
    
    public function setDenomination($pDenomination)
    {
        $this->denomination = $pDenomination;
    }

    // returns two dimensional ([Semster][Subject]) array of chosen classes (only)
    public function getSubjects():Array
    {
        $result = array();
        for ($i = 0; $i < count($this->selection); $i++) {
            $result[$i] = array();
            for($j = 0; $j < count($this->selection[$i]); $j++) {
                if($this->selection[$i][$j] != "0") {
                    array_push($result[$i], $this->getSchool()->getSubjects()[$j]);
                }
            }
        }
        return $result;
    }

    // returns two dimensional ([Semster][SelectionType]) array of selection type (abbreviation) of all subjects
    public function getSelectionTypes():Array
    {
        $result = array();
        for ($i = 0; $i < count($this->selection); $i++) {
            $result[$i] = array();
            for($j = 0; $j < count($this->selection[$i]); $j++) {
                //if($this->selection[$i][$j] != "0") {
                    array_push($result[$i], $this->getSchool()->getSelectionTypes()[$this->selection[$i][$j]]->getAbbreviation());
                //}
            }
        }
        return $result;
    }
}

?>