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
$f3->set('UPLOADS','../ImageUploads/'); // I had issues with sending uploaded files to abovewebroot, so this is a temporary fix which will be updated in beta

$web = \Web::instance(); // for file uploads from https://fatfreeframework.com/3.7/web

// Create a session, using the SQL session storage option (for details see https://fatfreeframework.com/3.6/session#SQL)
new \DB\SQL\Session($f3->get('DB'));

// multiple session variables help to provide the appropriate functions for each user
if (!$f3->exists('SESSION.name')) $f3->set('SESSION.name', 'UNSET');
if (!$f3->exists('SESSION.type')) $f3->set('SESSION.type', 'UNSET'); // shelter or cat lover
if (!$f3->exists('SESSION.email')) $f3->set('SESSION.email', 'UNSET');
if (!$f3->exists('SESSION.ID')) $f3->set('SESSION.ID', 'UNSET'); //unique user ID

  /////////////////////////////////////////////
 // Simple Example URL application routings //
/////////////////////////////////////////////


$f3->route('GET /',
  function ($f3) {

      $controller = new SimpleController; //required on every page to display the user's name on the nav bar when logged in
      $userid = $f3->get('SESSION.ID');
      $username = $controller->getUserName($userid);
      $f3->set('username', $username);

    $f3->set('html_title','Cat Lover');
    $f3->set('content','index.html');
    echo Template::instance()->render('layout.html');
  }
);


$f3->route('GET /signup',
    function($f3) {
    $f3->set('html_title','Sign up to Cat Lover');
    $f3->set('loginerror', 'Blank.html');
    $f3->set('signuperror', 'Blank.html');
    $f3->set('content','Signup.html');
    echo template::instance()->render('layoutnonav.html');
  }
);


$f3->route('POST /signup-complete',

    function($f3) {
    $controller = new SimpleController;
    $check = $controller->doesUserExist($f3->get('POST.email'));
      if ($check==true) {

          $f3->reroute('/signup-error');

      }
    else

        $formdata = array();
        $formdata["name"] = $f3->get('POST.name');
        $formdata["email"] = $f3->get('POST.email');
        $formdata["password"] = $f3->get('POST.password');
        $formdata["usertype"] = $f3->get('POST.usertype');

        $controller->putIntoDatabase($formdata);

        $f3->set('formData',$formdata);

        $f3->set('html_title','Signup Complete');
        $f3->set('content','SignupResponse.html');
        echo template::instance()->render('layoutnonav.html');
    }
);

$f3->route('GET /signup-error',
    function($f3) {

        $f3->set('html_title', 'Log in to Cat Lovers');
        $f3->set('loginerror', 'Blank.html');
        $f3->set('signuperror', 'SignupError.html'); //include error message
        $f3->set('content', 'Signup.html');
        echo template::instance()->render('layoutnonav.html');
    }
);

$f3->route('GET /login',
    function($f3) {

        $f3->set('html_title', 'Log in to Cat Lovers');
        $f3->set('loginerror', 'Blank.html');
        $f3->set('signuperror', 'Blank.html');
        $f3->set('content', 'Login.html');
        echo template::instance()->render('layoutnonav.html');
    }
);

$f3->route('POST /login',
    function($f3) {
        $controller = new SimpleController;
        if ($controller->loginUser($f3->get('POST.email'), $f3->get('POST.password'))) {// user is recognised
            $email = $f3->get('POST.email');
            $userid = $controller->getUserID($email);
            $usertype = $controller->getUserType($userid);
            $username = $controller->getUserName($userid);
            $f3->set('SESSION.name', $username);
            $f3->set('SESSION.type', $usertype);
            $f3->set('SESSION.email', $email);
            $f3->set('SESSION.ID', $userid);


            $f3->reroute('/your-profile');
        }
        else
            $f3->reroute('/login-error');		// return to login page with the message that there was an error in the credentials
    }
);

$f3->route('GET /login-error',
    function($f3) {

        $f3->set('html_title', 'Log in to Cat Lovers');
        $f3->set('loginerror', 'LoginError.html');
        $f3->set('signuperror', 'Blank.html');
        $f3->set('content', 'Login.html');
        echo template::instance()->render('layoutnonav.html');
    }
);

$f3->route('GET /logout',
    function($f3) {
        $f3->set('SESSION.name', 'UNSET');
        $f3->set('SESSION.type', 'UNSET');
        $f3->set('SESSION.email', 'UNSET');
        $f3->set('SESSION.ID', 'UNSET');
        $f3->reroute('/');
    }
);

$f3->route('POST /logout',
    function($f3) {
        $f3->set('SESSION.name', 'UNSET');
        $f3->set('SESSION.type', 'UNSET');
        $f3->set('SESSION.email', 'UNSET');
        $f3->set('SESSION.ID', 'UNSET');
        $f3->reroute('/');
    }
);

// arrays weren't working how I wanted so this is a workaround that I will try and fix in Beta
$f3->route('GET /your-profile',
    function($f3) {
        $controller = new SimpleController;
        $ID = $f3->get('SESSION.ID');
        $usertype = $controller->getUserType($ID);
        $username = $controller->getUserName($ID);
        $useremail = $controller->getUserEmail($ID);
        $userphone = $controller->getUserPhone($ID);
        $useraddress = $controller->getUserAddress($ID);
        $userweb = $controller->getUserWeb($ID);
        $userintro = $controller->getUserIntro($ID);
        $usercertified = $controller->getUserCertified($ID);
        $useravno = $controller->getUserAvNo($ID);

        $f3->set('username', $username);
        $f3->set('usertype', $usertype);
        $f3->set('useremail', $useremail);
        $f3->set('userphone', $userphone);
        $f3->set('useraddress', $useraddress);
        $f3->set('userweb', $userweb);
        $f3->set('userintro', $userintro);
        $f3->set('usercertified', $usercertified);
        $f3->set('avno', $useravno);

        $is = new ImageServer;
        $email = $f3->get('SESSION.email');
        $info = $is->infoServiceByUser($email);
        $f3->set('datalist', $info);

        $f3->set('html_title', 'Your profile');
        $f3->set('content', 'Profile.html');
        echo template::instance()->render('layout.html');
    }
);

$f3->route('GET /update-profile',
    function($f3) {

        $controller = new SimpleController;
        $ID = $f3->get('SESSION.ID');
        $usertype = $controller->getUserType($ID);
        $username = $controller->getUserName($ID);
        $avno = $controller->getUserAvNo($ID);
        $email = $controller->getUserEmail($ID);

        $f3->set('username', $username);
        $f3->set('usertype', $usertype);
        $f3->set('avno', $avno);
        $f3->set('useremail', $email);

        $f3->set('html_title', 'User home');
        $f3->set('content', 'UpdateProfile.html');
        $f3->set('success', 'Blank.html');
        $f3->set('restricted', 'true');
        echo template::instance()->render('layout.html');

    }
);

$f3->route('POST /update-name',

    function($f3) {

        $controller = new SimpleController;
        $userid = $f3->get('SESSION.ID');

        $controller->updateUserName($userid);

        $usertype = $controller->getUserType($userid);
        $username = $controller->getUserName($userid);
        $avno = $controller->getUserAvNo($userid);
        $email = $controller->getUserEmail($userid);

        $f3->set('username', $username);
        $f3->set('usertype', $usertype);
        $f3->set('avno', $avno);
        $f3->set('useremail', $email);

        $f3->set('html_title','Update successful');
        $f3->set('content','UpdateProfile.html');
        $f3->set('success', 'UpdateNameComplete.html');
        echo template::instance()->render('layout.html');
    }
);

$f3->route('POST /update-email',

    function($f3) {

        $controller = new SimpleController;
        $userid = $f3->get('SESSION.ID');

        $controller->updateUserEmail($userid);

        $usertype = $controller->getUserType($userid);
        $username = $controller->getUserName($userid);
        $avno = $controller->getUserAvNo($userid);
        $email = $controller->getUserEmail($userid);

        $f3->set('username', $username);
        $f3->set('usertype', $usertype);
        $f3->set('avno', $avno);
        $f3->set('useremail', $email);

        $f3->set('html_title','Update successful');
        $f3->set('content','UpdateProfile.html');
        $f3->set('success', 'UpdateEmailComplete.html');
        echo template::instance()->render('layout.html');
    }
);

$f3->route('POST /update-password',

    function($f3) {

        $controller = new SimpleController;
        $userid = $f3->get('SESSION.ID');

        $controller->updateUserPassword($userid);

        $usertype = $controller->getUserType($userid);
        $username = $controller->getUserName($userid);
        $avno = $controller->getUserAvNo($userid);
        $email = $controller->getUserEmail($userid);

        $f3->set('username', $username);
        $f3->set('usertype', $usertype);
        $f3->set('avno', $avno);
        $f3->set('useremail', $email);

        $f3->set('html_title','Update successful');
        $f3->set('content','UpdateProfile.html');
        $f3->set('success', 'UpdatePasswordComplete.html');
        echo template::instance()->render('layout.html');
    }
);

$f3->route('GET /share',
    function($f3) {
        $controller = new SimpleController;
        $userid = $f3->get('SESSION.ID');
        $username = $controller->getUserName($userid);
        $f3->set('username', $username);

        $is = new ImageServer;
        $info = $is->infoService(0);
        $f3->set('datalist', $info);
        $f3->set('html_title', 'Share');
        $f3->set('content', 'Share.html');
        echo template::instance()->render('layout.html');
    }
);


$f3->route('GET /home',
    function($f3) {
        $f3->reroute('/');  }
);

//////////////////////////
// Pages coming in Beta //
//////////////////////////

$f3->route('GET /message-board',
    function($f3) {
        $controller = new SimpleController;
        $userid = $f3->get('SESSION.ID');
        $username = $controller->getUserName($userid);
        $f3->set('username', $username);
        $f3->set('html_title','Message Board');
        $f3->set('content','MessageBoard.html');
        echo template::instance()->render('layoutnonav.html');
    }
);

$f3->route('GET /help-advice',
    function($f3) {
        $controller = new SimpleController;
        $userid = $f3->get('SESSION.ID');
        $username = $controller->getUserName($userid);
        $f3->set('username', $username);
        $f3->set('html_title','Help and advice');
        $f3->set('content','HelpAdvice.html');
        echo template::instance()->render('layoutnonav.html');
    }
);

$f3->route('GET /agency-certification',
    function($f3) {
        $controller = new SimpleController;
        $userid = $f3->get('SESSION.ID');
        $username = $controller->getUserName($userid);
        $f3->set('username', $username);
        $f3->set('html_title','Apply for Agency Certification');
        $f3->set('content','AgencyCert.html');
        echo template::instance()->render('layoutnonav.html');
    }
);

  ////////////////////////
 // Image server bits  //
////////////////////////

// When using GET, provide a form for the user to upload an image via the file input type
$f3->route('GET /upload',
    function($f3) {
        $controller = new SimpleController;
        $userid = $f3->get('SESSION.ID');
        $username = $controller->getUserName($userid);
        $f3->set('username', $username);
        $f3->set('html_title','Image Server Upload');
        $f3->set('uploaderror', 'Blank.html');
        $f3->set('content','upload.html');
        echo template::instance()->render('layout.html');
    }
);

// When using POST (e.g. upload form is submitted), upload the image, then display
// some info about it via uploaded.html template
$f3->route('POST /upload',
    function($f3) {
        $controller = new SimpleController;
        $userid = $f3->get('SESSION.ID');
        $username = $controller->getUserName($userid);
        $f3->set('username', $username);

        $is = new ImageServer;
        if ($filedata = $is->upload()) {						// if this is null, upload failed
            $f3->set('filedata', $filedata);

            $f3->set('html_title','Image Server Home');
            $f3->set('content','uploaded.html');
            echo template::instance()->render('layout.html');
        }
    }
);

// If quiet is given, don't output any page content, but echo image data
// -- intended as AJAX interface, e.g. for mobile app
$f3->route('GET|POST /upload/quiet',
    function($f3) {
        $is = new ImageServer;
        $filedata = $is->upload();
        echo json_encode($filedata);
    }
);

$f3->route('GET /uploaded',
    function($f3) {
        $f3->set('html_title','Image Server Home');
        $f3->set('content','uploaded.html');
        echo template::instance()->render('layout.html');
    }
);


// infoService() just returns an array of info about the images, which here is JSON encoded
// and then echoed e.g. for use by AJAX calls (or debugging)
// If @id is missing or 0, all images, otherwise just the one nominated by @id
$f3->route('GET|POST /infoservice',
    function($f3) {
        $is = new ImageServer;
        $info = $is->infoService(0);
        echo json_encode($info);
    }
);

$f3->route('GET|POST /infoservice/@id',
    function($f3) {
        $is = new ImageServer;
        $info = $is->infoService($f3->get('PARAMS.id'));
        echo json_encode($info);
    }
);


// image and thumb generate a pure image for the original picture or thumbnail, respectively,
// copied directly from the file stored above the web root
// -- the 2nd parameter of showImage() specifies whether thumbnail (if true) or not
$f3->route('GET|POST /image/@id',
    function($f3) {
        $is = new ImageServer;
        $is->showImage($f3->get('PARAMS.id'), false);
    }
);

$f3->route('GET|POST /thumb/@id',
    function($f3) {
        $is = new ImageServer;
        $is->showImage($f3->get('PARAMS.id'), true);
    }
);


// For GET delete requests, we show the viewimages page again, now without the deleted image
$f3->route('GET /delete/@id',
    function($f3) {
        $is = new ImageServer;
        $is->deleteService($f3->get('PARAMS.id'));
        $f3->reroute('/viewimages');
    }
);

// For POST delete requests (presumably AJAX), we do not output any page content
$f3->route('POST /delete/@id',
    function($f3) {
        $is = new ImageServer;
        $is->deleteService($f3->get('PARAMS.id'));
    }
);


////////////////////////////////////
//Pages that might be used in beta//
////////////////////////////////////


//$f3->route('POST /upload-cat',
//    function($f3) {
//        $formdata = array();
//        $formdata["catname"] = $f3->get('POST.catname');
//        $formdata["sex"] = $f3->get('POST.sex');
//        $formdata["age"] = $f3->get('POST.age');
//        $formdata["breed"] = $f3->get('POST.breed');
//        $formdata["visualdesc"] = $f3->get('POST.visualdesc');
//        $formdata["personalitydesc"] = $f3->get('POST.personalitydesc');
//        $formdata["shelter"] = $f3->get('SESSION.name');
//        $formdata["account"] = $f3->get('SESSION.email');

//        $controller = new CatController;
//       $controller->putIntoDatabase($formdata);

//        $f3->set('formData',$formdata);

//        $f3->set('html_title','Upload cat complete');
//        $f3->set('content','UploadCatComplete.html');
//        $f3->set('restricted', 'true');
//        echo template::instance()->render('layout.html');
//    }

//);

// $f3->route('GET /view-cats',
//    function($f3) {
//        $is = new ImageServer;
//        $info = $is->infoService(0);
//        $f3->set('datalist', $info);
//        $f3->set('content', 'ViewCats.html');
//        echo template::instance()->render('layout.html');
//    }
//);


// $f3->route('GET /cat-database',
//   function($f3) {
//        $controller = new CatController;
//        $alldata = $controller->getData();

//        $f3->set("dbData", $alldata);
//        $f3->set('html_title','Cat database');
//        $f3->set('content','CatDatabase.html');
//        $f3->set('restricted', 'true');
//        echo template::instance()->render('layout.html');
//    }
//);


//$f3->route('GET /user-home/your-cats',
//    function($f3) {
//       $controller = new CatController;
//        $account = $f3->get('SESSION.email');
//        $yourcatdata = $controller->getYourCatData($account);

//        $f3->set("dbData", $yourcatdata);
//        $f3->set('html_title','Your cats');
//        $f3->set('content','YourCats.html');
//        $f3->set('restricted', 'true');
//        echo template::instance()->render('layout.html');
//    }
//);

//$f3->route('GET /cat-form',
//    function($f3) {
//
//        $f3->set('html_title', 'Cat form');
//        $f3->set('content', 'CatForm.html');
//        $f3->set('restricted', 'true');
//        echo template::instance()->render('layout.html');

//    }
//);

////////////////////////
// Run the F3 engine //
////////////////////////

// Run the FFF engine
$f3->run();

?>