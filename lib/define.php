<?php
session_start();

function d($const, $value){
	return defined(strtoupper($const)) or define(strtoupper($const), $value);
}

// If there is a local define, use that
if (is_file(ROOT . '/lib/define-local.php'))
	include_once(ROOT . '/lib/define-local.php');

// Defines
d('PATH', '/social/');
d('API_KEY', 'yfzglacwsx'); //content.wayne.edu

// Include the API
include_once(ROOT . '/lib/phpcms/phpcms.php');
include_once(ROOT . '/lib/functions.php');

// Initialize the API
$c = new Phpcms(API_KEY);
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
		$user_details = $c->sendRequest('api/user/info', array('accessid' => $_POST['accessid']));

		// Save the basic information about this user
		$_SESSION['user_details'] = $user_details['response']['user'];

		// Set the user message
		Flash('Welcome back ' . $_SESSION['user_details']['first_name'] . '!', 'success');
	}else{
		// Set the user error message
		Flash('Incorrect AccessID/password. Please try again', 'error');
	}
	
	//Pre($login_response);
}

// if trying to logout
if (isset($_GET['logout'])){
	// Set the user message
	Flash('See you next time ' . $_SESSION['user_details']['first_name'] . '!', 'success');

	// Unset the session
	unset($_SESSION['sessionid']);	
	unset($_SESSION['user_details']);

	// Redirect to homepage
	header('Location:' . PATH);
	die();
}

// Set the sessionID if in the session
if (isset($_SESSION['sessionid'])){
	$c->setSession($_SESSION['sessionid']);
}

// Setup defaults
$page_title = 'Home';