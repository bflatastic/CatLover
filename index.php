<?php

  /////////////////////////////////////
 // index.php for SimpleExample app //
/////////////////////////////////////

// Create f3 object then set various global properties of it
// These are available to the routing code below, but also to any 
// classes defined in autoloaded definitions

$f3 = require('../../../AboveWebRoot/fatfree-master-3.7/lib/base.php');

// autoload Controller class(es) and anything hidden above web root, e.g. DB stuff
$f3->set('AUTOLOAD','autoload/;../../../AboveWebRoot/autoload/');

$db = DatabaseConnection::connect();		// defined as autoloaded class in AboveWebRoot/autoload/
$f3->set('DB', $db);

$f3->set('DEBUG',3);		// set maximum debug level
$f3->set('UI','ui/');		// folder for View templates
$f3->set('UPLOADS','../../../AboveWebRoot/ImageUploads/');

$web = \Web::instance(); // for file uploads from https://fatfreeframework.com/3.7/web

// Create a session, using the SQL session storage option (for details see https://fatfreeframework.com/3.6/session#SQL)
new \DB\SQL\Session($f3->get('DB'));
// if the SESSION.username variable is not set, set it to 'UNSET'
if (!$f3->exists('SESSION.name')) $f3->set('SESSION.name', 'UNSET');
if (!$f3->exists('SESSION.type')) $f3->set('SESSION.type', 'UNSET');
if (!$f3->exists('SESSION.email')) $f3->set('SESSION.email', 'UNSET');

  /////////////////////////////////////////////
 // Simple Example URL application routings //
/////////////////////////////////////////////

//home page (index.html) -- actually just shows form entry page with a different title
$f3->route('GET /',
  function ($f3) {
    $f3->set('html_title','Cat Lover');
    $f3->set('content','Home.html');
    echo Template::instance()->render('layout.html');
  }
);

// When using GET, provide a form for the user to upload an image via the file input type
$f3->route('GET /signup',
  function($f3) {
    $f3->set('html_title','Sign up');
    $f3->set('content','Signup.html');
    echo template::instance()->render('layout.html');
  }
);


// When using POST (e.g.  form is submitted), invoke the controller, which will process
// any data then return info we want to display. We display
// the info here via the SignupResponse.html template
$f3->route('POST /signup-complete',

    function($f3) {
        $formdata = array();			// array to pass on the entered data in
        $formdata["name"] = $f3->get('POST.name');			// whatever was called "name" on the form
        $formdata["email"] = $f3->get('POST.email');
        $formdata["password"] = $f3->get('POST.password');
        $formdata["usertype"] = $f3->get('POST.usertype');

        $controller = new SimpleController;
        $controller->putIntoDatabase($formdata);

        $f3->set('formData',$formdata);		// set info in F3 variable for access in response template

        $f3->set('html_title','Signup Complete');
        $f3->set('content','SignupResponse.html');
        echo template::instance()->render('layout.html');
    }
);

// When using GET, provide a form for the user to log in to a simple F3-managed session
$f3->route('GET /login',
    function($f3) {

        $f3->set('html_title', 'Log in to Cat Lovers');
        $f3->set('content', 'Login.html');		// the login form that will be shown to the user
        echo template::instance()->render('layout.html');
    }
);

// When using POST, do the login and session management
$f3->route('POST /login',
    function($f3) {
        $controller = new SimpleController;
        if ($controller->loginUser($f3->get('POST.email'), $f3->get('POST.password'))) {// user is recognised
            $email = $f3->get('POST.email');
            $usertype = $controller->getUserType($email);
            $username = $controller->getUserName($email);
            $f3->set('SESSION.name', $username);
            $f3->set('SESSION.type', $usertype);
            $f3->set('SESSION.email', $email);


            $f3->reroute('/user-home');
        }
        else
            $f3->reroute('/login/error');		// return to login page with the message that there was an error in the credentials
    }
);

$f3->route('GET /login/error',
    function($f3) {

        $f3->set('html_title', 'Log in to Cat Lovers');
        $f3->set('content', 'LoginError.html');
        echo template::instance()->render('layout.html');
    }
);

$f3->route('POST /logout',
    function($f3) {
        $f3->set('SESSION.name', 'UNSET');
        $f3->set('SESSION.type', 'UNSET');
        $f3->set('SESSION.email', 'UNSET');
        $f3->reroute('/login');		// return to login page with the message that the user has been logged out
    }
);


$f3->route('GET /upload-cat',
    function($f3) {
        $f3->set('html_title','Upload a cat');
        $f3->set('content','UploadCat.html');
        $f3->set('restricted_shelter', 'true');
        echo template::instance()->render('layout.html');
    }
);

$f3->route('POST /upload-cat',
    function($f3) {
        $formdata = array();			// array to pass on the entered data in
        $formdata["catname"] = $f3->get('POST.catname');			// whatever was called "name" on the form
        $formdata["sex"] = $f3->get('POST.sex');
        $formdata["age"] = $f3->get('POST.age');
        $formdata["dob"] = $f3->get('POST.dob');
        $formdata["breed"] = $f3->get('POST.breed');
        $formdata["visualdesc"] = $f3->get('POST.visualdesc');
        $formdata["personalitydesc"] = $f3->get('POST.personalitydesc');
        $formdata["shelter"] = $f3->get('SESSION.name');
        $formdata["account"] = $f3->get('SESSION.email');

        $controller = new CatController;
        $controller->putIntoDatabase($formdata);

        $f3->set('formData',$formdata);		// set info in F3 variable for access in response template

        $f3->set('html_title','Upload cat complete');
        $f3->set('content','UploadCatComplete.html');
        $f3->set('restricted', 'true');
        echo template::instance()->render('layout.html');
    }

);

$f3->route('POST /upload',
    function($f3) {
        $is = new ImageServer;
        if ($filedata = $is->upload()) { // if this is null, upload failed
            $f3->set('filedata', $filedata);

            $f3->set('html_title','Upload complete');
            $f3->set('content','UploadCatComplete.html');
            echo template::instance()->render('layout.html');
        }
    }
);

$f3->route('GET /view-cats',
    function($f3) {
        $is = new ImageServer;
        $info = $is->infoService(0);
        $f3->set('datalist', $info);
        $f3->set('content', 'ViewCats.html');
        echo template::instance()->render('layout.html');
    }
);

$f3->route('GET /dataView',
  function($f3) {
  	$controller = new SimpleController;
    $alldata = $controller->getData();
    
    $f3->set("dbData", $alldata);
    $f3->set('html_title','Viewing the data');
    $f3->set('content','dataView.html');
    $f3->set('restricted', 'true');
    echo template::instance()->render('layout.html');
  }
);

$f3->route('GET /cat-database',
    function($f3) {
        $controller = new CatController;
        $alldata = $controller->getData();

        $f3->set("dbData", $alldata);
        $f3->set('html_title','Cat database');
        $f3->set('content','CatDatabase.html');
        $f3->set('restricted', 'true');
        echo template::instance()->render('layout.html');
    }
);


$f3->route('GET /user-home',
    function($f3) {

        $f3->set('html_title', 'User home');
        $f3->set('content', 'CatUserCentre.html');
        $f3->set('restricted', 'true');
        echo template::instance()->render('layout.html');

    }
);

$f3->route('GET /user-home/your-cats',
    function($f3) {
        $controller = new CatController;
        $account = $f3->get('SESSION.email');
        $yourcatdata = $controller->getYourCatData($account);

        $f3->set("dbData", $yourcatdata);
        $f3->set('html_title','Your cats');
        $f3->set('content','YourCats.html');
        $f3->set('restricted', 'true');
        echo template::instance()->render('layout.html');
    }
);

$f3->route('POST /user-home',
    function($f3) {

        $f3->set('html_title', 'User home');
        $f3->set('content', 'CatUserCentre.html');
        $f3->set('restricted', 'true');
        echo template::instance()->render('layout.html');

    }
);

$f3->route('GET /editView',				// exactly the same as dataView, apart from the template used
  function($f3) {
  	$controller = new SimpleController;
    $alldata = $controller->getData();
    
    $f3->set("dbData", $alldata);
    $f3->set('html_title','Viewing the data');
    $f3->set('content','editView.html');
    echo template::instance()->render('layout.html');
  }
);

$f3->route('POST /editView',		// this is used when the form is submitted, i.e. method is POST
  function($f3) {
  	$controller = new SimpleController;
    $controller->deleteFromDatabase($f3->get('POST.toDelete'));		// in this case, delete selected data record

	$f3->reroute('/editView');  }		// will show edited data (GET route)
);

$f3->route('GET /home',
    function($f3) {
        $f3->reroute('/');  }
);

  ////////////////////////
 // Run the F3 engine //
////////////////////////

$f3->run();