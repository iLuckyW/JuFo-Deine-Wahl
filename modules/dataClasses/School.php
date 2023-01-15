<?php

class School
{
    /* schools name */
    private $name;

    /* selectable Subjects in this School */
    private $subjects;

    /* possible types to select a subject */
    private $selectionTypes;


    /* constructor */
    public function __construct($pName, $pSubjects, $pSelectionTypes)
    {
        $this->setName($pName);
        $this->setSubjects($pSubjects);
		$this->setSelectionTypes($pSelectionTypes);
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

    /* subjects getter and setter */
    public function getSubjects():Array
    {
        return $this->subjects;
    }

    public function getSubjectNames():Array
    {
        for ($i = 0; $i < count($this->subjects); $i++) {
            $subjectNames[$i] = $this->subjects[$i]->getName();
        }
        return $subjectNames;
    }

    public function getSubjectTags():Array
    {
        $array = array();

        for ($i = 0; $i < count($this->subjects); $i++) {
            array_push($array, $this->subjects[$i]->getTags());
        }
        return $array;
    }
    
    private function setSubjects($pSubjects)
    {
        // null is a possible value
		if ($pSubjects != null && !is_array($pSubjects)) throw new Exception("subjects needs to be an array");

		// if array, all elements need to be a subject
        if ($pSubjects != null && count(array_filter($pSubjects, function ($entry) {
            return !($entry instanceof Subject);
        })) > 0) throw new Exception("all objects in array need to be a subject");
		
		// set subject
        $this->subjects = $pSubjects;
    }

    /* selectionTypes getter and setter */
    public function getSelectionTypes():Array
    {
        return $this->selectionTypes;
    }

    public function getSelectionTypeAbbr():Array
    {
        for ($i = 0; $i < count($this->selectionTypes); $i++) {
            $selectionTypes[$i] = $this->selectionTypes[$i]->getAbbreviation();
        }
        return $selectionTypes;
    }
     
    private function setSelectionTypes($pSelectionTypes)
    {
        // null is a possible value
		if ($pSelectionTypes != null && !is_array($pSelectionTypes)) throw new Exception("selectionTypes needs to be an array");
		
		// if array, all elements need to be a selection type
        if ($pSelectionTypes != null && count(array_filter($pSelectionTypes, function ($entry) {
            return !($entry instanceof SelectionType);
        })) > 0) throw new Exception("all objects in array need to be a selection type");
		
		// set selection
        $this->selectionTypes = $pSelectionTypes;
    }
}

?>