<?php

require_once("School.php");
require_once("Student.php");
require_once("SelectionType.php");

class Selection
{
    // Converts one Semester-Selection into a two dimensional array
    // [0] -> subjects as Subjects
    // [1] -> selection type (for that subject) as SelectionType
    public static function makeReadable($pSelection, $pSchool)
    {
        $array = array(new ArrayObject(), new ArrayObject());
        
        // Check every subject in selection/school
        for ($i = 0; $i < count($pSelection); $i++)
        {
            // if the subject was selected
            if ($pSelection[$i] != "0")
            {
                // look for selection type of subject
                $j = 0;
                while ($j < count($pSchool->getSelectionTypes()) && $pSelection[$i] != $pSchool->getSelectionTypes()[$j]->getCharCode())
                {
                    $j++;
                }
                // if found add subejct and selection type to array
                if ($pSelection[$i] == $pSchool->getSelectionTypes()[$j]->getCharCode())
                {
                    $array[0]->append($pSchool->getSubjects()[$i]);
                    $array[1]->append($pSchool->getSelectionTypes()[$j]);
                }
                else throw new Exception("wrong char code for selection type found");
            }
        }

        return $array;
    }

    // return all posbbile Selections Types for a given subect index (in that school)
    public static function possibleSelections($pSubject, $pSchool)
    {
        $possibleSelections = new ArrayObject();

        for ($i = 0; $i < count($pSchool->getSelectionTypes()); $i++)
        {
            if ($pSchool->getPossibleSelections()[$pSubject][$i] == 1) $possibleSelections->append($pSchool->getSelectionTypes()[$i]);
        }

        return $possibleSelections;
    }

    // checks wheter the given semetser selection is valid for the given School
    public static function selectionsValid($pSelection, $pSchool)
    {
        $i = 0;
        while ($i < count($pSelection) && $pSchool->getPossibleSelections()[$i][Selection::selectionTypeCharCodeToIndex($pSelection[$i], $pSchool)] == 1)
        {
            $i++;
        }
        
        return $i >= count($pSelection);
    }

    // checks wheter you can select "that" selection type for "that" subject at "that" school
    private static function selectionValid($pSelectionType, $pSubject, $pSchool)
    {
        return $pSchool->getPossibleSelections[$pSubject][$pSelectionType] == 1;
    }

    // converts the name (as string) of a subject to its index for that school
    public static function subjectToIndex($pSubject, $pSchool)
    {
        // checks every subject, if name is equal to prameter
        $i = 0;
        while ($i < count($pSchool->getSubjects()) && $pSchool->getSubjects()[$i]->getName() != $pSubject)
        {
            $i++;
        }
        
        return $i;
    }

    // converts the char code of a selection type to its index for that school
    public static function selectionTypeCharCodeToIndex($pSelectionType, $pSchool)
    {
        // checks every selection type, if char code is equal to prameter
        $i = 0;
        while ($i < count($pSchool->getSelectionTypes()) && $pSchool->getSelectionTypes()[$i]->getCharCode() != $pSelectionType)
        {
            $i++;
        }
        return $i;
    }
}

?>