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

    public function getUserType($data) {
        $this->mapper->load(['Email=?',$data]);
        return $this->mapper['User_type'];
    }

    public function getUserName($data)
    {
        $this->mapper->load(['Email=?', $data]);
        return $this->mapper['Name'];
    }

    

}