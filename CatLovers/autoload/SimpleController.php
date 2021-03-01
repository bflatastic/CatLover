<?php
// Class that provides methods for working with the form data.
// There should be NOTHING in this file except this class definition.

class SimpleController {
	private $mapper;

	public function __construct() {
		global $f3;						// needed for $f3->get()
		$this->mapper = new DB\SQL\Mapper($f3->get('DB'),'UserDatabase');	// create DB query mapper object

    }

	public function putIntoDatabase($data) {
		$this->mapper->Name = $data["name"];					// set value for "name" field
		$this->mapper->Email = $data["email"];
		$this->mapper->Password =$data["password"];
		$this->mapper->User_type = $data["usertype"];
		$this->mapper->Adopting = $data["adopting"];
		$this->mapper->save();									// save new record with these fields
	}

    public function updateUserName($userid) {
        global $f3;						// needed for $f3->get()
        $this->mapper->load(['ID=?', $userid]);

        $update = $f3->get('POST.updateName');
        $this->mapper->Name = $update;

        $this->mapper->update();
    }

    public function updateUserEmail($userid) {
        global $f3;						// needed for $f3->get()
        $this->mapper->load(['ID=?', $userid]);

        $update = $f3->get('POST.updateEmail');
        $this->mapper->Email = $update;

        $this->mapper->update();
    }

    public function updateUserPassword($userid, $data) {
        global $f3;						// needed for $f3->get()
        $this->mapper->load(['ID=?', $userid]);

        $update = $f3->get('POST.updatePassword');
        $this->mapper->Password = $update;

        $this->mapper->update();
    }

	public function getData() {
		$list = $this->mapper->find();
		return $list;
	}

	public function deleteFromDatabase($idToDelete) {
		$this->mapper->load(['id=?', $idToDelete]);				// load DB record matching the given ID
		$this->mapper->erase();									// delete the DB record
	}

    public function loginUser($user, $password) {		// very simple login -- no use of encryption, hashing etc.
        $auth = new \Auth($this->mapper, array('id'=>'Email', 'pw'=>'Password'));
        return $auth->login($user, $password); 			// returns true on successful login

    }

    public function getUserData($data)
    {
        $this->mapper->load(['Email=?',$data]);
        return $recordData = array();
                $recordData["Name"] = $this->mapper["Name"];
                $recordData["Email"] = $this->mapper["Email"];
                $recordData["User_type"] = $this->mapper["User_type"];
                $recordData["Adopting"] = $this->mapper["Adopting"];
                $recordData["Join_date"] = $this->mapper["Join_date"];
                $recordData["Certified"] = $this->mapper["Certified"];
                $recordData["Address"] = $this->mapper["Address"];
                $recordData["Website"] = $this->mapper["Website"];
                $recordData["Introduction"] = $this->mapper["Introduction"];
                array_push($recordData);

            return $recordData;

    }

    // help from https://stackoverflow.com/questions/65559704/error-500-with-call-to-a-member-function-on-bool
    public function doesUserExist($data)
    {
        $user = $this->mapper->load(['Email=?',$data]);

        if($user == false) {
            return false; }
        else
            return true;
    }

    public function getUserType($data) {
        $this->mapper->load(['ID=?',$data]);
        return $this->mapper['User_type'];
    }

    public function getUserName($data)
    {
        $this->mapper->load(['ID=?', $data]);
        return $this->mapper['Name'];
    }

    public function getUserEmail($data)
    {
        $this->mapper->load(['ID=?', $data]);
        return $this->mapper['Email'];
    }

    public function getUserPhone($data)
    {
        $this->mapper->load(['ID=?', $data]);
        return $this->mapper['Telephone'];
    }

    public function getUserAddress($data)
    {
        $this->mapper->load(['ID=?', $data]);
        return $this->mapper['Address'];
    }

    public function getUserWeb($data)
    {
        $this->mapper->load(['ID=?', $data]);
        return $this->mapper['Website'];
    }

    public function getUserIntro($data)
    {
        $this->mapper->load(['ID=?', $data]);
        return $this->mapper['Introduction'];
    }

    public function getUserID($data)
    {
        $this->mapper->load(['Email=?', $data]);
        return $this->mapper['ID'];
    }

    public function getUserCertified($data)
    {
        $this->mapper->load(['ID=?', $data]);
        return $this->mapper['Certified'];
    }

    public function getUserAvNo($data)
    {
        $this->mapper->load(['ID=?', $data]);
        return $this->mapper['AvNo'];
    }





}