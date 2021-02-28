<?php

// Class that provides methods for working with the form data.
// There should be NOTHING in this file except this class definition.

class CatController
{
    private $mapper;

    public function __construct()
    {
        global $f3;                        // needed for $f3->get()
        $this->mapper = new DB\SQL\Mapper($f3->get('DB'), "CatAdopteesDatabase");    // create DB query mapper object
    }

    public function putIntoDatabase($data)
    {
        global $f3;
        $this->mapper->Name = $data["catname"];                    // set value for "name" field
        $this->mapper->Sex = $data["sex"];
        $this->mapper->Age = $data["age"];
        $this->mapper->DoB = $data["dob"];
        $this->mapper->Breed = $data["breed"];
        $this->mapper->VisualDescription = $data["visualdesc"];
        $this->mapper->PersonalityDescription = $data["personalitydesc"];
        $this->mapper->Shelter = $data["shelter"];
        $this->mapper->Account = $data["account"];
        $this->mapper->save();                                    // save new record with these fields
    }

    public function getData()
    {
        $list = $this->mapper->find();
        return $list;
    }

    public function getYourCatData($data)
    {
        $list = $this->mapper->find(['Account=?',$data]);
        return $list;
    }

    public function deleteFromDatabase($idToDelete)
    {
        $this->mapper->load(['id=?', $idToDelete]);                // load DB record matching the given ID
        $this->mapper->erase();                                    // delete the DB record
    }

}


