<?php
session_start();

function d($const, $value){
	return defined(strtoupper($const)) or define(strtoupper($const), $value);
}

// If there is a local define, use that
if (is_file(ROOT . '/lib/define-local.php'))
	include_once(ROOT . '/lib/define-local.php');

// Defines
$root_array = explode('/', ROOT);
d('PATH', '/' . array_pop($root_array) . '/');

// Find the default mode
if (!isset($_SESSION['api_mode']))
	$_SESSION['api_mode'] = (strstr($_SERVER['HTTP_HOST'], 'www-dev') === false)?'production':'dev';

// Flop the API if needed
if (isset($_GET['api']) && $_GET['api'] == 'flop'){
	$_SESSION['api_mode'] = ($_SESSION['api_mode'] == 'dev')?'production':'dev';
	header('location:' . $_SERVER['HTTP_REFERER']);
	die();
}

d('MODE', $_SESSION['api_mode']); // Use the right API
d('API_KEY', ($_SESSION['api_mode'] == 'dev')?'bcppxparnj':'vnjjhwwtrp'); // i.wayne.edu

// Include the API
include_once(ROOT . '/lib/phpcms/phpcms.php');
include_once(ROOT . '/lib/functions.php');

// Initialize the API
$c = new Phpcms(API_KEY, MODE);
$c->debug = false;
//$c->parser = 'raw';

//$c->sendRequest('api/information/version', 'get');
//Pre($c);

// If trying to login
if (isset($_POST['accessid']) && isset($_POST['password'])){
	$login_credentials = array('accessid' => strtolower($_POST['accessid']), 'password' => $_POST['password']);
	$login_response = $c->sendRequest('api/user/auth', $login_credentials , 'post', true);
	
	Pre($login_response);

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