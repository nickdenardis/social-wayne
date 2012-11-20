<?php
	define('ROOT', dirname(__FILE__));
	include_once(ROOT . '/lib/define.php');
	
	// oAuth stuff
	include_once(ROOT . '/lib/themattharris/tmhOAuth.php');
	include_once(ROOT . '/lib/themattharris/tmhUtilities.php');
	
	// Page stuff
	$page_title = 'New Tweet';
	$page_url = $_SERVER['PHP_SELF'];
	
	// If submitting a new tweet 
	if (isset($_POST) && count($_POST) > 0){
		// Make sure there is an account selected
		if (is_array($_POST['from_account'])){
			foreach($_POST['from_account'] as $account){		
				// Create the params array
				$tweet_params = array('message' => $_POST['new_message'],
									'account_id' => $account['account_id'],
									'user_id' => $_SESSION['user_details']['user_id'],
									'date_scheduled' => date('Y-m-d H:i:s'));
				
				// Get a list of all the account this user has access to
				$tweet_status[] = $c->sendRequest('socialy/tweet/schedule', $tweet_params, 'post');
			}
			
			$success_num = 0;
			foreach($tweet_status as $tweeted){
				$success_num++;
			}
			Flash('Successfully tweeted from ' . $success_num . ' account(s)');			
		}else{
			Flash('Please select an account.', 'error');
		}
		
	}
	// Get a list of all the account this user has access to
	$account_list = $c->sendRequest('socialy/account/access', array(), 'get');
	
	include_once(ROOT . '/_header.php');
?>	
<div class="row-fluid" id="content">
	<form accept-charset="UTF-8" action="" method="post">
	<div class="span9">
		<div class="list-view">
			<div class="list-header well">
				<h2>Add Tweet</h2>
			</div>
			        <textarea class="span10" id="new_message" name="new_message" placeholder="Type in your message" rows="5"></textarea>
			        <h6>320 characters remaining</h6>
			        <button class="btn btn-info" type="submit">Post New Message</button>
		</div>
	</div>
	
	<div class="span3 add-account">
		<div class="list-view">
			<div class="list-header well">
				<h2>Post to Account</h2>
			</div>
			<ul>
				<?php
				// List the users accounts
				if (is_array($account_list['response']['accounts'])){
					foreach($account_list['response']['accounts'] as $account){
						if ($account['level'] != 'view'){
							echo '<li>';
							echo '<img src="' . h($account['profile_image_url_https']) . '" width="48" height="48" alt="' . h($account['screen_name']) . '" /><br />';
							echo '<input type="checkbox" value="' . h($account['account_id']) . '" name="from_account[]"> ';
							echo h($account['name']) . '<br /><a href="http://twitter.com/' . h($account['screen_name']) . '">@' . h($account['screen_name']) . '</a>';
							echo '</li>';
						}
					}
				}else{
				?>
				<li><a href="<?php echo PATH; ?>accounts/listing?start=1"><img src="<?php echo PATH; ?>img/twitter_signin.png" alt="twitter_signin" width="150" height="22" /></a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
	</form>
</div>

<?php
	include_once(ROOT. '/_footer.php');
?>