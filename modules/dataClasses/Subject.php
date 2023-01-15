<?php

class Subject
{
    /* subjects name */
    private $name;

    // subjects tags
    const tag = array("slk" => "sprachlich-literarisch-künstlerisches Aufgabenfeld", "gws" => "gesellschaftswissenschaftliches Aufgabenfeld",
                                "mnt" => "mathematisch-naturwissenschaftlich-technisches Aufgabenfeld", "son" => "Weitere Aufgabenfelder", "fsp" => "Fremdsprache", "ltk" => "Literaturkurs",
                                "pjk" => "Projektkurs", "bil" => "bilinguales Fach", "vtf" => "Vertiefungsfach", "pfl" => "Pflichtfach", "lks" => "LK-Fach", "zsk" => "Zusatzkurs",
                                "nmd" => "nur mündlich", "abs" => "Als Abifach belegbar", "nfs" => "Neue Fremdsprach ab Sek 2", "rel" => "Religion");
    
    private $tags;
    
    /* constructor */
    public function __construct($pName, $pTags)
    {
        $this->setName($pName);
        $this->setTags($pTags);
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

    /* tags getter and setter */
    public function getTags():Array
    {
        return $this->tags;
    }
    
    private function setTags($pTags)
    {
        $this->tags = $pTags;
    }

    public function hasTag($pTag):bool
    {
        foreach($this->tags as $tag) {
            if(strcmp($pTag, $tag)==0) return true;
        }
        return false;
    }
}

?>