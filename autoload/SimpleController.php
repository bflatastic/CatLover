<?php
// Class that provides methods for working with the form data.
// There should be NOTHING in this file except this class definition.

class SimpleController {
	private $mapper;
	private $usertable = "UserDatabase";

	public function __construct() {
		global $f3;						// needed for $f3->get()
		$this->mapper = new DB\SQL\Mapper($f3->get('DB'),'UserDatabase');	// create DB query mapper object

    }

	public function putIntoDatabase($data) {
		$this->mapper->Name = $data["name"];					// set value for "name" field
		$this->mapper->Email = $data["email"];
		$this->mapper->Password =$data["password"];
		$this->mapper->Location = $data["location"];
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

    public function updateUserLocation($userid) {
        global $f3;						// needed for $f3->get()
        $this->mapper->load(['ID=?', $userid]);

        $update = $f3->get('POST.updateLocation');
        $this->mapper->Location = $update;

        $this->mapper->update();
    }

    public function updateUserAv($userid) {
        global $f3;						// needed for $f3->get()
        $this->mapper->load(['ID=?', $userid]);

        $update = $f3->get('POST.updateAv');
        $this->mapper->AvNo = $update;

        $this->mapper->update();
    }

	public function getData() {
		$list = $this->mapper->find();
		return $list;
	}
    public function deleteAccount($idToDelete){
        $this->mapper->load(['id=?', $idToDelete]);				// load DB record matching the given ID
        $this->mapper->erase();
        // delete the DB record
    }

	public function authAccount($user, $password) {
        $auth = new \Auth($this->mapper, array('id'=>'Email', 'pw'=>'Password'));
        return $auth->login($user, $password);

	}

    public function loginUser($user, $password) {		// very simple login -- no use of encryption, hashing etc.
        $auth = new \Auth($this->mapper, array('id'=>'Email', 'pw'=>'Password'));
        return $auth->login($user, $password); 			// returns true on successful login

    }

    public function userInfoService($userID) {
        global $f3;
        $returnData = array();
        $user=new DB\SQL\Mapper($f3->get('DB'),$this->usertable);	// create DB query mapper object
        $list = $user->find();

        $user->load(['ID=?',$userID]);
        $recordData = array();
        $recordData["ID"] = $user["ID"];
        $recordData["name"] = $user["Name"];
        $recordData["email"] = $user["Email"];
        $recordData["adopting"] = $user["Adopting"];
        $recordData["avno"] = $user["AvNo"];
        $recordData["joindate"] = $user["Join_date"];
        $recordData["location"] = $user["Location"];
        $recordData["telephone"] = $user["Telephone"];
        $recordData["introduction"] = $user["Introduction"];
        return $recordData;
    }

    public function userInfoServiceByEmail($useremail)
    {
        global $f3;
        $returnData = array();
        $user = new DB\SQL\Mapper($f3->get('DB'), $this->usertable);    // create DB query mapper object
        $list = $user->find();

        $user->load(['Email=?', $useremail]);
        $recordData = array();
        $recordData["name"] = $user["Name"];
        $recordData["email"] = $user["Email"];
        $recordData["adopting"] = $user["Adopting"];
        $recordData["avno"] = $user["AvNo"];
        $recordData["joindate"] = $user["Join_date"];
        $recordData["location"] = $user["Location"];
        $recordData["telephone"] = $user["Telephone"];
        $recordData["introduction"] = $user["Introduction"];
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

    public function getUserLocation($data) {
        $this->mapper->load(['ID=?',$data]);
        return $this->mapper['Location'];
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


    public function getUserAvNo($data)
    {
        $this->mapper->load(['ID=?', $data]);
        return $this->mapper['AvNo'];
    }


}