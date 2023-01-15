<?php

class Teacher
{
    /* teachers name */
    private $name;

    /* teachers grade */
    private $grade;

    /* teachers school */
    private $school;

    public function __construct($pName, $pGrade, $pSchool)
    {
        $this->setName($pName);
        $this->setGrade($pGrade);
        $this->setSchool($pSchool);
    }

    /* name getter and setter */
    public function getName():String
    {
        return $this->name;
    }

    public function setName($pName)
    {
        $this->name = trim($pName);
    }

    /* grade getter and setter */
    public function getGrade():String
    {
        return $this->grade;
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
}