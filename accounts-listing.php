<?php
	define('ROOT', dirname(__FILE__));
	include_once(ROOT . '/lib/define.php');
	
	// oAuth stuff
	include_once(ROOT . '/lib/themattharris/tmhOAuth.php');
	include_once(ROOT . '/lib/themattharris/tmhUtilities.php');
	
	// Page stuff
	$page_title = 'Accounts';
	$page_url = $_SERVER['PHP_SELF'];
	
	// If adding access
	if (isset($_POST['add_access']) && count($_POST['add_access']) > 0){
		// Loop through every account submitted
		foreach($_POST['add_access'] as $account_id => $accessid){
			// If there was a user submitted
			if ($accessid != ''){
				// Create the params to submit
				$access_params = array('account_id' => $account_id, 'accessid' => $accessid, 'level' => $_POST['add_level'][$account_id]);
	
				// Do the API request to add their access
				$access_response = $c->sendRequest('socialy/access/add', $access_params, 'post', true);
		    
				// If there was an error saving the account to the API
				if (is_array($access_response['response']['error']))
		    		Flash($access_response['response']['error']['message'], 'error');
		    	else
		    		Flash('User added successfully.', 'success');
		    }
		}
	}
	
	// if removing access
	if (isset($_GET['remove'])){
		$remove_params = array('account_id' => (int)$_GET['remove']);
		$account_removed = $c->sendRequest('socialy/account/remove', $remove_params, 'post');
		
		if (is_array($account_removed['response']['error']))
	    	Flash($account_removed['response']['error']['message'], 'error');
		else
			Flash('The account has been removed.');
			
		header('Location: ' . tmhUtilities::php_self());
		die();
	}
	
	// if removing access
	if (isset($_GET['remove_access']) && isset($_GET['user_id'])){
		$remove_params = array('account_id' => (int)$_GET['remove_access'], 'user_id' => (int)$_GET['user_id']);
		$access_removed = $c->sendRequest('socialy/access/remove', $remove_params, 'post');
		
		if (is_array($access_removed['response']['error']))
	    	Flash($access_removed['response']['error']['message'], 'error');
		else
			Flash('The access has been removed.');
			
		header('Location: ' . tmhUtilities::php_self());
		die();
	}
	
	$tmhOAuth = new tmhOAuth(array(
	  'consumer_key'    => 'AHbD6KOwFcUp57JJ8ofTw',
	  'consumer_secret' => 'lxwPpSvedHIEQpevtPChGPuZk4awsVWECx5wUwE3R4',
	));
	
	function outputError($tmhOAuth) {
	  Flash('There was an error: ' . $tmhOAuth->response['response']);
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
	  global $c;
	  
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
	    
	    // Save the credentials in the DB
	    $socialy_params = $_SESSION['access_token'];
	    $socialy_params['owner_id'] = $_SESSION['user_details']['user_id'];
	    $socialy_params['type'] = 'twitter';
	    $socialy_params['is_active'] = '1';
	    $social_response = $c->sendRequest('socialy/account/add', $socialy_params, 'post', true);
	    
	    // If there was an error saving the account to the API
	    if (is_array($social_response['response']['error']))
	    	Flash($social_response['response']['error']['message'], 'error');
	    	
	    // Try to get the info for the account
	    $tmhOAuth->config['user_token']  = $social_response['response']['account']['oauth_token'];
	    $tmhOAuth->config['user_secret'] = $social_response['response']['account']['oauth_token_secret'];
	    $code = $tmhOAuth->request('GET',$tmhOAuth->url('1/account/verify_credentials'));
	
	    // If this user is valid
	    if ($code == 200) {
	    	// Get the response
		    $resp = json_decode($tmhOAuth->response['response'], true);
		    
		    // Update it in the twitter account table
		    $account_params = $resp;
		    $account_params['account_id'] = $social_response['response']['account']['account_id'];
		    $account_params['owner_id'] = $_SESSION['user_details']['user_id'];
		    unset($account_params['status']);
		    
		    $account_response = $c->sendRequest('socialy/twitterinfo/add', $account_params, 'post', true);
		    
		    // If there was an error saving the account to the API
		    if (is_array($account_response['response']['error']))
	    		Flash($account_response['response']['error']['message'], 'error');
	    	else	
		    	Flash('Account "' . $account_response['response']['twitter']['screen_name'] . '" added successfully!');
	    }
	    
	    header('Location: ' . tmhUtilities::php_self());
	    die();
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
	
	// Get a list of all the account this user has access to
	$account_list = $c->sendRequest('socialy/account/listing', array(), 'get');
	
	Pre($account_list);
	
	
	$access_list = $c->sendRequest('socialy/account/access', array('notowner' => 'true'), 'get');
	
	//Pre($access_list);
	
	include_once(ROOT . '/_header.php');
?>	
<div class="row-fluid" id="content">
	<div class="span9">
		<div class="list-view">
			<div class="list-header well">
				<form id="search" class="form-search" method="post" action="">
					<input type="text" class="query input-large search-query " name="search" value="" placeholder="Search Accounts" autocomplete="off" maxlength="50">
				</form>
			</div>
			
			<?php
				// List the users accounts
				if (!array_key_exists('error', $account_list['response']) && is_array($account_list['response']['accounts'])){
					echo '<form id="account-access" class="form-account-access form-inline" method="post" action="">';
					echo '<ul class="account-list">';
					foreach($account_list['response']['accounts'] as $account){
						echo '<li>';
						echo '<img src="' . h($account['profile_image_url_https']) . '" width="48" height="48" alt="' . h($account['screen_name']) . '" />';
						echo '<div class="controls controls-row add-access">
						<select class="span4" name="add_level[' . h($account['account_id']) . ']"><option value="view">View</option><option value="edit">Propose</option><option value="approve">Auto Approve</option></select>
						<input type="text" class="input-small" name="add_access[' . h($account['account_id']) . ']" value="" placeholder="AccessID" autocomplete="off" maxlength="24"> <button type="submit" class="btn">Add Access</button></div>';
						echo '<h2>' . h($account['name']) . ' (<a href="http://twitter.com/' . h($account['screen_name']) . '">@' . h($account['screen_name']) . '</a>) <span class="label label-inverse"><i class="icon-ban-circle icon-white"></i> <a href="?remove=' . $account['account_id'] . '">Remove</a></span></h2>';
						echo '<span class="stats">' . h($account['statuses_count']) . ' Tweets | ' . h($account['friends_count']) . ' Following | ' . h($account['followers_count']) . ' Followers</span>';
						
						if (is_array($account['access']) && count($account['access']) > 1){
							echo '<ul class="access-list">';
							foreach($account['access'] as $user){
								if ($user['user_id'] != $_SESSION['user_details']['user_id']){
									echo '<li>' . $user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['accessid'] . ') <span class="label remove"><a href="?remove_access=' . $account['account_id'] . '&amp;user_id=' . $user['user_id'] . '"><i class="icon-remove-sign icon-white"></i> ' . $user['level'] . '</a></span></li>';
									}
							}
							echo '</ul>';
						}
						echo '</li>';
						
					}
					echo '</ul>';
					echo '</form>';
				}else{ ?>
					<p>Please add an account on the right side.</p>	
			<?php }
			?>
			
			<div class="list-header well">
				<h2>Accounts shared with you</h2>
			</div>
			<?php
				// List the users accounts
				if (!array_key_exists('error', $access_list['response']) && is_array($access_list['response']['accounts']) && count($access_list['response']['accounts']) > 0){
					echo '<ul class="account-list">';
					foreach($access_list['response']['accounts'] as $account){
						echo '<li>';
						echo '<img src="' . h($account['profile_image_url_https']) . '" width="48" height="48" alt="' . h($account['screen_name']) . '" />';
						echo '<h2>' . h($account['name']) . ' (<a href="http://twitter.com/' . h($account['screen_name']) . '">@' . h($account['screen_name']) . '</a>)</h2>';
						echo '<span class="stats">' . h($account['statuses_count']) . ' Tweets | ' . h($account['friends_count']) . ' Following | ' . h($account['followers_count']) . ' Followers <span class="label remove">' . $account['level'] . '</span></span>';
						echo '</li>';
					}
					echo '</ul>';
				}else{ ?>
					<p>Currently no accounts shared with you.</p>	
			<?php } ?>
		</div>
	</div>
	
	<div class="span3 add-account">
		<div class="list-view">
			<div class="list-header well">
				<h2>Add an Account</h2>
			</div>
			<ul>
				<li><a href="?start=1"><img src="<?php echo PATH; ?>img/twitter_signin.png" alt="twitter_signin" width="150" height="22" /></a></li>
			</ul>
		</div>
	</div>
</div>

<?php
	include_once(ROOT. '/_footer.php');
?>