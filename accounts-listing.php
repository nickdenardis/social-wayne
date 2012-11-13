<?php
	define('ROOT', dirname(__FILE__));
	include_once(ROOT . '/lib/define.php');
	
	$page_title = 'Accounts';
	$page_url = $_SERVER['PHP_SELF'];
	
	/**
	 * Demonstration of the OAuth authorize flow only. You would typically do this
	 * when an unknown user is first using your application and you wish to make
	 * requests on their behalf.
	 *
	 * Instead of storing the token and secret in the session you would probably
	 * store them in a secure database with their logon details for your website.
	 *
	 * When the user next visits the site, or you wish to act on their behalf,
	 * you would use those tokens and skip this entire process.
	 *
	 * Instructions:
	 * 1) If you don't have one already, create a Twitter application on
	 *      https://dev.twitter.com/apps
	 * 2) From the application details page copy the consumer key and consumer
	 *      secret into the place in this code marked with (YOUR_CONSUMER_KEY
	 *      and YOUR_CONSUMER_SECRET)
	 * 3) Visit this page using your web browser.
	 *
	 * @author themattharris
	 */
	
	include_once(ROOT . '/lib/themattharris/tmhOAuth.php');
	include_once(ROOT . '/lib/themattharris/tmhUtilities.php');
	$tmhOAuth = new tmhOAuth(array(
	  'consumer_key'    => 'AHbD6KOwFcUp57JJ8ofTw',
	  'consumer_secret' => 'lxwPpSvedHIEQpevtPChGPuZk4awsVWECx5wUwE3R4',
	));
	
	function outputError($tmhOAuth) {
	  echo 'There was an error: ' . $tmhOAuth->response['response'] . PHP_EOL;
	}
	
	function wipe() {
	  session_destroy();
	  header('Location: ' . tmhUtilities::php_self());
	}
	
	
	// Step 1: Request a temporary token
	function request_token($tmhOAuth) {
	  $code = $tmhOAuth->request(
	    'POST',
	    $tmhOAuth->url('oauth/request_token', ''),
	    array(
	      'oauth_callback' => tmhUtilities::php_self()
	    )
	  );
	
	  if ($code == 200) {
	    $_SESSION['oauth'] = $tmhOAuth->extract_params($tmhOAuth->response['response']);
	    authorize($tmhOAuth);
	  } else {
	    outputError($tmhOAuth);
	  }
	}
	
	
	// Step 2: Direct the user to the authorize web page
	function authorize($tmhOAuth) {
	  $authurl = $tmhOAuth->url("oauth/authorize", '') .  "?oauth_token={$_SESSION['oauth']['oauth_token']}";
	  header("Location: {$authurl}");
	
	  // in case the redirect doesn't fire
	  echo '<p>To complete the OAuth flow please visit URL: <a href="'. $authurl . '">' . $authurl . '</a></p>';
	}
	
	
	// Step 3: This is the code that runs when Twitter redirects the user to the callback. Exchange the temporary token for a permanent access token
	function access_token($tmhOAuth) {
	  $tmhOAuth->config['user_token']  = $_SESSION['oauth']['oauth_token'];
	  $tmhOAuth->config['user_secret'] = $_SESSION['oauth']['oauth_token_secret'];
	
	  $code = $tmhOAuth->request(
	    'POST',
	    $tmhOAuth->url('oauth/access_token', ''),
	    array(
	      'oauth_verifier' => $_REQUEST['oauth_verifier']
	    )
	  );
	
	  if ($code == 200) {
	    $_SESSION['access_token'] = $tmhOAuth->extract_params($tmhOAuth->response['response']);
	    unset($_SESSION['oauth']);
	    header('Location: ' . tmhUtilities::php_self());
	  } else {
	    outputError($tmhOAuth);
	  }
	}
	
	
	// Step 4: Now the user has authenticated, do something with the permanent token and secret we received
	function verify_credentials($tmhOAuth) {
	  $tmhOAuth->config['user_token']  = $_SESSION['access_token']['oauth_token'];
	  $tmhOAuth->config['user_secret'] = $_SESSION['access_token']['oauth_token_secret'];
	
	  $code = $tmhOAuth->request(
	    'GET',
	    $tmhOAuth->url('1/account/verify_credentials')
	  );
	
	  if ($code == 200) {
	    $resp = json_decode($tmhOAuth->response['response']);
	    print_r($resp);
	    print_r($_SESSION);
	    echo '<h1>Hello ' . $resp->screen_name . '</h1>';
	    echo '<p>The access level of this token is: ' . $tmhOAuth->response['headers']['x_access_level'] . '</p>';
	  } else {
	    outputError($tmhOAuth);
	  }
	}
	
	if (isset($_REQUEST['start'])) :
	  request_token($tmhOAuth);
	elseif (isset($_REQUEST['oauth_verifier'])) :
	  access_token($tmhOAuth);
	elseif (isset($_REQUEST['verify'])) :
	  verify_credentials($tmhOAuth);
	elseif (isset($_REQUEST['wipe'])) :
	  wipe();
	endif;
	
	include_once(ROOT . '/_header.php');
?>	
<div class="row-fluid" id="content">
	<div class="span8">
		<div class="list-view">
			<div class="list-header well">
				<form id="search" class="form-search" method="post" action="">
					<input type="text" class="query input-large search-query " name="search" value="" placeholder="Search Accounts" autocomplete="off" maxlength="50">
				</form>
				<p>
				<?php if (isset($_SESSION['access_token'])) : ?>
				  There appears to be some credentials already stored in this browser session.
				  Do you want to <a href="?verify=1">verify the credentials?</a> or
				  <a href="?wipe=1">wipe them and start again</a>.
				<?php else : ?>
				  <a href="?start=1"><img src="<?php echo PATH; ?>img/twitter_signin.png" alt="twitter_signin" width="150" height="22" /></a>.
				<?php endif; ?>
				</p>
			</div>
			
		</div>
	</div>
	
</div>

<?php
	include_once(ROOT. '/_footer.php');
?>