<?php
// Class that provides methods for working with the images.  The constructor is empty, because
// initialisation isn't needed; in fact it probably never really needs to be instanced and all
// could be done with static methods.
class ImageServer {
	private $filedata;
	private $uploadResult = "Upload failed! (unknown reason) <a href=''>Return</a>";
	private $pictable = "PhotoUploads";
	private $acceptedTypes = ["image/jpeg", "image/png", "image/gif"];	// tiff and svg removed: image processing code can't handle them

	public function __construct() {}

	// abandoned experiment with a ::instance() method similar to the ones used elsewhere in F3
	// -- it works, but seems to have no advantages
	// 	public static function instance() {
	// 		return new self;
	// 	}

	// Puts the file data into the DB
	public function store() {
		global $f3;			// because we need f3->get()
		$pic = new DB\SQL\Mapper($f3->get('DB'),$this->pictable);	// create DB query mapper object
		$pic->picname = $this->filedata["title"];
		$pic->picfile = $this->filedata["name"];
		$pic->pictype = $this->filedata["type"];
		$pic->save();
	}

	// Upload file, using callback to get data, then copy data into local array.
	// Call store() to store data, call createThumbnail(), add thumb name to the
	// array then return the array
	public function upload() {
		global $f3;		// so that we can call functions like $f3->set() from inside here

		$overwrite = true; // set to true, to overwrite an existing file; Default: false
		$slug = true; // rename file to filesystem-friendly version

		Web::instance()->receive(function($file,$anything){

			$this->filedata = $file;		// export file data to outside this function

			// maybe you want to check the file size
			if($this->filedata['size'] > (5 * 1024 * 1024)) {		// if bigger than 2 MB
				$this->uploadResult = "Please choose a file under 5MB.  <a href=''>Return</a>";
				return false; // this file is not valid, return false will skip moving it
			}
			if(!in_array($this->filedata['type'], $this->acceptedTypes)) {		// if not an approved type
				$this->uploadResult = "Please upload a jpg, png or gif file.  <a href=''>Return</a>";
				return false; // this file is not valid, return false will skip moving it
			}
			// everything went fine, hurray!
			$this->uploadResult = "success";
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

		$picname = $f3->get('POST.picname');
		$this->filedata["title"] = $picname;		// add the title to filedata for later use
		$this->store();

		return $this->filedata;
	}


	// This just returns all the data we have about images in the DB, just as an array.
	// If given no argument, it uses the default argument, 0, and in this case it returns data about all images.
	// If given an image ID as argument (there can be no image with ID 0), it returns data only about that image.
	public function infoService($picID=0) {
		global $f3;
		$returnData = array();
		$pic=new DB\SQL\Mapper($f3->get('DB'),$this->pictable);	// create DB query mapper object
		$list = $pic->find();
		if ($picID == 0) {
			foreach ($list as $record) {
				$recordData = array();
				$recordData["picfile"] = $record["picfile"];
				$recordData["pictype"] = $record["pictype"];
				$recordData["picname"] = $record["picname"];
				$recordData["picdesc"] = $record["picdesc"];
				$recordData["picusername"] = $record["picusername"];
				$recordData["picaccount"] = $record["picaccount"];
				$recordData["piclabel"] = $record["piclabel"];
				$recordData["picID"] = $record["ID"];
				array_push(	$returnData, $recordData);
			}
			return $returnData;
		}
		$pic->load(['ID=?',$picID]);
		$recordData = array();
		$recordData["picfile"] = $pic["picfile"];
		$recordData["pictype"] = $pic["pictype"];
		$recordData["picname"] = $pic["picname"];
		$recordData["picdesc"] = $pic["picdesc"];
		$recordData["picusername"] = $pic["picusername"];
		$recordData["picaccount"] = $pic["picaccount"];
		$recordData["piclabel"] = $pic["piclabel"];
		$recordData["picID"] = $pic["ID"];
		return $recordData;
	}

	public function infoServiceByUser($email) {
		global $f3;
		$returnData = array();
		$pic=new DB\SQL\Mapper($f3->get('DB'),$this->pictable);	// create DB query mapper object
		$list = $pic->find(['picaccount=?', $email]);
			foreach ($list as $record) {
				$recordData = array();
				$recordData["picfile"] = $record["picfile"];
				$recordData["pictype"] = $record["pictype"];
				$recordData["picname"] = $record["picname"];
				$recordData["picdesc"] = $record["picdesc"];
				$recordData["picusername"] = $record["picusername"];
				$recordData["picaccount"] = $record["picaccount"];
				$recordData["piclabel"] = $record["piclabel"];
				$recordData["picID"] = $record["ID"];
				array_push(	$returnData, $recordData);
			}
			return $returnData;
	}

	// Delete data record about the image, and remove its file and thumbnail file
	public function deleteService($picID) {
		global $f3;
		$pic=new DB\SQL\Mapper($f3->get('DB'),$this->pictable);	// create DB query mapper object
		$pic->load(['ID=?',$picID]);							// load DB record matching the given ID
		unlink($pic["picfile"]);										// remove the image file
		$pic->erase();													// delete the DB record
	}




}
?>
