<?php
session_start();

// Defines
define('PATH', '/social-wayne/');

// Include the API
include_once($_SERVER['DOCUMENT_ROOT'] . PATH . 'lib/phpcms/phpcms.php');
include_once($_SERVER['DOCUMENT_ROOT'] . PATH . 'lib/functions.php');

// Initialize the API
//$c = new Phpcms('yfzglacwsx');
$c = new Phpcms('ahombstqkr');
$c->debug = false;
//$c->parser = 'raw';

//$c->sendRequest('api/information/version', 'get');
//Pre($c);

// If trying to login
if (isset($_POST['accessid']) && isset($_POST['password'])){
	$login_credentials = array('accessid' => strtolower($_POST['accessid']), 'password' => $_POST['password']);
	$login_response = $c->sendRequest('api/user/auth', $login_credentials , 'post', true);

	// Store the session ID in the session
	if (isset($login_response['response']['auth']['sessionid'])){
		$_SESSION['sessionid'] = $login_response['response']['auth']['sessionid'];
		
		// Get the users information
		Pre($c->sendRequest('api/user/info', array('accessid' => $_POST['accessid'])));
		
		Flash('You are now logged in', 'success');
	}else{
		Flash('Incorrect AccessID/password. Please try again', 'error');
	}
	
	//Pre($login_response);
}

// if trying to logout
if (isset($_GET['logout'])){
	unset($_SESSION['sessionid']);	
}

// Set the sessionID if in the session
if (isset($_SESSION['sessionid'])){
	$c->setSession($_SESSION['sessionid']);
}