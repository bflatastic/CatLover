<?php

// Class that provides methods for working with the form data.
// There should be NOTHING in this file except this class definition.

class CatController
{
    private $mapper;
    private $filedata;
    private $uploadResult = "Upload failed! (unknown reason) <a href=''>Return</a>";
    private $cattable = "CatAdopteesDatabase";
    private $ownertable = "UserDatabase";
    private $acceptedTypes = ["image/jpeg", "image/png", "image/gif"];	// tiff and svg removed: image processing code can't handle them

    public function __construct()
    {
        global $f3;                        // needed for $f3->get()
        $this->mapper = new DB\SQL\Mapper($f3->get('DB'), "CatAdopteesDatabase");

    }

    public function getData()
    {
        $list = $this->mapper->find();
        return $list;
    }

    public function getCatID($data)
    {
        $list = $this->mapper->load(['ID=?',$data]);
        return $list;
    }

    public function deleteFromDatabase($idToDelete)
    {
        $this->mapper->load(['id=?', $idToDelete]);                // load DB record matching the given ID
        $this->mapper->erase();                                    // delete the DB record
    }



    public function store() {
        global $f3;			// because we need f3->get()
        $pic = new DB\SQL\Mapper($f3->get('DB'),$this->cattable);	// create DB query mapper object
        $pic->catname = $this->filedata["catname"];
        $pic->malefemale = $this->filedata["malefemale"];
        $pic->age = $this->filedata["age"];
        $pic->breed = $this->filedata["breed"];
        $pic->visualdesc = $this->filedata["visualdesc"];
        $pic->personalitydesc = $this->filedata["personalitydesc"];
        $pic->ownerID = $this->filedata["ownerID"];
        $pic->picfile = $this->filedata["name"];
        $pic->pictype = $this->filedata["type"];
        $pic->save();
    }

    private function compressImage($filename, $type) {
        switch ($type) {
            case "jpeg":
                $image = imagecreatefromjpeg($filename);
                imagejpeg($image, $filename, 10);
                break;
            case "png":
                $image = imagecreatefrompng($filename);
                imagepng($image, $filename,1,PNG_NO_FILTER);
                break;
            case "gif":
                $image = imagecreatefromgif($filename);
                imagejpeg($image, $filename, 10);
                break;
        }

    }

    // Upload file, using callback to get data, then copy data into local array.
    // Call store() to store data, call createThumbnail(), add thumb name to the
    // array then return the array
    public function UploadCatPhoto() {
        global $f3;		// so that we can call functions like $f3->set() from inside here

        $overwrite = true; // set to true, to overwrite an existing file; Default: false
        $slug = true; // rename file to filesystem-friendly version

        Web::instance()->receive(function($file,$anything){

            $this->filedata = $file;		// export file data to outside this function

            // Check size
            if($this->filedata['size'] > (5 * 1024 * 1024)) {		// if bigger than 2 MB
                $this->uploadResult = "Please choose a file under 5MB.  <a href=''>Return</a>";
                return false; // this file is not valid, return false will skip moving it
            }

            // Check type
            if(!in_array($this->filedata['type'], $this->acceptedTypes)) {		// if not an approved type
                $this->uploadResult = "Please upload a jpg, png or gif file.  <a href=''>Return</a>";
                return false; // this file is not valid, return false will skip moving it
            }

            // everything went fine, hurray!
            $this->uploadResult = "success";
            $this->compressImage($this->filedata['name'],$this->filedata['type']);
            return true; // allows the file to be moved from php tmp dir to your defined upload dir
        },
            $overwrite,
            $slug
        );
        // 	var_dump($this->filedata);

        if ($this->uploadResult != "success") {
            echo $this->uploadResult;				// ideally this might be output from index.php
            return null;
        }


        $catname = $f3->get('POST.catname');
        $this->filedata["catname"] = $catname;
        $malefemale = $f3->get('POST.malefemale');
        $this->filedata["malefemale"] = $malefemale;
        $age = $f3->get('POST.age');
        $this->filedata["age"] = $age;
        $breed = $f3->get('POST.breed');
        $this->filedata["breed"] = $breed;
        $visualdesc = $f3->get('POST.visualdesc');
        $this->filedata["visualdesc"] = $visualdesc;
        $personalitydesc = $f3->get('POST.personalitydesc');
        $this->filedata["personalitydesc"] = $personalitydesc;
        $ownerID = $f3->get('POST.ownerID');
        $this->filedata["ownerID"] = $ownerID;
        $this->store();


        return $this->filedata;
    }
    public function getOwnerName($data)
    {
        global $f3;
        $owner=new DB\SQL\Mapper($f3->get('DB'),$this->ownertable);	// create DB query mapper object
        $owner->load(['ID=?', $data]);
        return $owner['Name'];
    }

    public function getOwnerAvNo($data)
    {
        global $f3;
        $owner=new DB\SQL\Mapper($f3->get('DB'),$this->ownertable);	// create DB query mapper object
        $owner->load(['ID=?', $data]);
        return $owner['AvNo'];
    }

    public function catInfoService($catID=0) {
        global $f3;
        $returnData = array();
        $cat=new DB\SQL\Mapper($f3->get('DB'),$this->cattable);	// create DB query mapper object
        $owner=new DB\SQL\Mapper($f3->get('DB'),$this->ownertable);	// create DB query mapper object
        $list = $cat->find();
        if ($catID == 0) {
            foreach ($list as $record) {
                $recordData = array();
                $recordData["picfile"] = $record["picfile"];
                $recordData["pictype"] = $record["pictype"];
                $recordData["catname"] = $record["catname"];
                $recordData["malefemale"] = $record["malefemale"];
                $recordData["age"] = $record["age"];
                $recordData["breed"] = $record["breed"];
                $recordData["visualdesc"] = $record["visualdesc"];
                $recordData["personalitydesc"] = $record["personalitydesc"];
                $recordData["label"] = $record["label"];
                $recordData["location"] = $record["location"];
                $recordData["ID"] = $record["ID"];
                $recordData["ownerID"] = $record["ownerID"];
                $recordData["owneravno"] =  $this->getOwnerAvNo($recordData["ownerID"]);
                $recordData["ownername"] =  $this->getOwnerName($recordData["ownerID"]);
                array_push(	$returnData, $recordData);
            }
            return $returnData;

        }
        $cat->load(['ID=?',$catID]);
        $recordData = array();
        $recordData["picfile"] = $cat["picfile"];
        $recordData["pictype"] = $cat["pictype"];
        $recordData["catname"] = $cat["catname"];
        $recordData["malefemale"] = $cat["malefemale"];
        $recordData["age"] = $cat["age"];
        $recordData["breed"] = $cat["breed"];
        $recordData["visualdesc"] = $cat["visualdesc"];
        $recordData["personalitydesc"] = $cat["personalitydesc"];
        $recordData["label"] = $cat["label"];
        $recordData["location"] = $cat["location"];
        $recordData["ID"] = $cat["ID"];
        $recordData["ownerID"] = $cat["ownerID"];
        $recordData["owneravno"] =  $this->getOwnerAvNo($recordData["ownerID"]);
        $recordData["ownername"] =  $this->getOwnerName($recordData["ownerID"]);

        return $recordData;
    }

    public function catInfoServiceByUser($ownerID) {
        global $f3;
        $returnData = array();
        $cat=new DB\SQL\Mapper($f3->get('DB'),$this->cattable);	// create DB query mapper object
        $list = $cat->find(['ownerID=?', $ownerID]);
            foreach ($list as $record) {
                $recordData = array();
                $recordData["picfile"] = $record["picfile"];
                $recordData["pictype"] = $record["pictype"];
                $recordData["catname"] = $record["catname"];
                $recordData["malefemale"] = $record["malefemale"];
                $recordData["age"] = $record["age"];
                $recordData["breed"] = $record["breed"];
                $recordData["visualdesc"] = $record["visualdesc"];
                $recordData["personalitydesc"] = $record["personalitydesc"];
                $recordData["label"] = $record["label"];
                $recordData["location"] = $record["location"];
                $recordData["ID"] = $record["ID"];
                $recordData["ownerID"] = $record["ownerID"];
                $recordData["owneravno"] =  $this->getOwnerAvNo($recordData["ownerID"]);
                $recordData["ownername"] =  $this->getOwnerName($recordData["ownerID"]);

                array_push(	$returnData, $recordData);
            }

            return $returnData;


    }

//    public function deletingAccount($ownerID)
//    {
//        global $f3;
//        $list = $this->mapper->load(array('ownerID=?', $ownerID));
//        foreach($list as $record) {
//            $this->mapper->$record["username"] = "i";
//            $this->mapper->save();
//        }
//        unset($record);
//    }

    public function getOwnerID($catID)
    {
        $this->mapper->load(['ID=?', $catID]);
        return $this->mapper['ownerID'];
    }

    public function updateOwnerName($ownerID)
    {
        global $f3;                        // needed for $f3->get()
        $this->mapper->load(array('ownerID=?', $ownerID));
        $update = $f3->get('POST.updateName');
        $this->mapper->username = $update;

        $this->mapper->update();
    }

    public function updateCatName($catid) {
        global $f3;						// needed for $f3->get()
        $this->mapper->load(['ID=?', $catid]);

        $update = $f3->get('POST.updateCatName');
        $this->mapper->catname = $update;

        $this->mapper->update();
    }

    public function updateCatAge($catid) {
        global $f3;						// needed for $f3->get()
        $this->mapper->load(['ID=?', $catid]);

        $update = $f3->get('POST.updateCatAge');
        $this->mapper->age = $update;

        $this->mapper->update();
    }

    public function updateCatBreed($catid) {
        global $f3;						// needed for $f3->get()
        $this->mapper->load(['ID=?', $catid]);

        $update = $f3->get('POST.updateCatBreed');
        $this->mapper->breed = $update;

        $this->mapper->update();
    }

    public function updateCatLocation($catid) {
        global $f3;						// needed for $f3->get()
        $this->mapper->load(['ID=?', $catid]);

        $update = $f3->get('POST.updateLocation');
        $this->mapper->location = $update;

        $this->mapper->update();
    }

    public function updateVisualDesc($catid) {
        global $f3;						// needed for $f3->get()
        $this->mapper->load(['ID=?', $catid]);

        $update = $f3->get('POST.updateVisualDesc');
        $this->mapper->visualdesc = $update;

        $this->mapper->update();
    }

    public function updatePersonalityDesc($catid) {
        global $f3;						// needed for $f3->get()
        $this->mapper->load(['ID=?', $catid]);

        $update = $f3->get('POST.updatePersonalityDesc');
        $this->mapper->personalitydesc = $update;

        $this->mapper->update();
    }

    public function updateCatLabel($catid) {
        global $f3;						// needed for $f3->get()
        $this->mapper->load(['ID=?', $catid]);

        $update = $f3->get('POST.updateLabel');
        $this->mapper->label = $update;

        $this->mapper->update();
    }

}


