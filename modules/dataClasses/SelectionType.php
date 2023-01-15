<?php

class SelectionType
{
    /* selectionTypes name */
    private $name;

    /* selectionTypes abbreviation */
    private $abbreviation;
    
    /* constructor */
    public function __construct($pName, $pAbbreviation)
    {
        $this->setName($pName);
        $this->setAbbreviation($pAbbreviation);
    }
    
    /* name getter and setter */
    public function getName():String
    {
        return $this->name;
    }
    
    private function setName($pName)
    {
        $this->name = trim($pName);
    }

    /* abbreviation getter and setter */
    public function getAbbreviation():String
    {
        return $this->abbreviation;
    }
    
    private function setAbbreviation($pAbbreviation)
    {
        $this->abbreviation = trim($pAbbreviation);
    }

    // toString and toSelectionType conversion
    public function toString():String
    {
        return $this->getName() . ", " . $this->getAbbreviation();
    }

    static function toSelectionType($str):SelectionType
    {
        $attributes = explode(", ", $str);
        return new SelectionType($attributes[0], $attributes[1]);
    }
}

?>