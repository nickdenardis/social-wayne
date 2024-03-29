<?php
	define('ROOT', dirname(__FILE__));
	include_once(ROOT . '/lib/define.php');
	
	// oAuth stuff
	include_once(ROOT . '/lib/themattharris/tmhOAuth.php');
	include_once(ROOT . '/lib/themattharris/tmhUtilities.php');
	
	// Page stuff
	$page_title = 'New Tweet';
	$page_url = $_SERVER['PHP_SELF'];
	$page_css = array(PATH . 'vendor/datepicker/css/datepicker.css', PATH . 'vendor/timepicker/css/timepicker.css');
	$page_js = array(PATH . 'vendor/datepicker/js/bootstrap-datepicker.js', PATH . 'vendor/timepicker/js/bootstrap-timepicker.js');
	
	// If submitting a new tweet 
	if (isset($_POST) && count($_POST) > 0){

		// Make sure there is an account selected
		if (array_key_exists('from_account', $_POST) && is_array($_POST['from_account'])){
			foreach($_POST['from_account'] as $account){
				Pre($account);		
				$date_scheduled = date('Y-m-d H:i:s', strtotime($_POST['tweet-date'] . ' ' . $_POST['tweet-time']));
				
				// Create the params array
				$tweet_params = array('message' => $_POST['new_message'],
									'account_id' => $account,
									'user_id' => $_SESSION['user_details']['user_id'],
									'date_scheduled' => $date_scheduled);
				Pre($tweet_params);
				
				// Get a list of all the account this user has access to
				$tweet_status[] = $c->sendRequest('socialy/tweet/schedule', $tweet_params, 'post');
			}
			
			Pre($tweet_status);
			
			foreach($tweet_status as $tweeted){
				if (array_key_exists('error', $tweeted['response'])){
					Flash('Error posting to [account name].', 'error');
				}else{
					Flash('Successfully tweeted from [account name] account.');		
				}
			}	
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
			<div class="row-fluid">
				<div class="span11">
					<textarea class="span10" id="new_message" name="new_message" placeholder="Type in your message" rows="5"></textarea>
					<h6>320 characters remaining</h6>
					        
					<div class="row-fluid">
						<div class="span3">
							<button class="btn btn-info" type="submit">Post New Message</button>
						</div>
							<div class="input-append date datepicker span2" data-date="<?php echo date('Y-m-d'); ?>" data-date-format="yyyy-mm-dd">
								<input class="span11" size="16" type="text" value="<?php echo date('Y-m-d'); ?>" name="tweet-date">
								<span class="add-on"><i class="icon-th"></i></span>
							</div>
						<div class="span6">
							<div class="input-append bootstrap-timepicker-component">
				            	<input class="input-small timepicker" type="text" value="<?php echo date('g:i a'); ?>" name="tweet-time" /><span class="add-on"><i class="icon-time"></i></span>
				            </div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="span3 account-selection">
		<div class="list-view">
			<div class="list-header well">
				<h2>Post to Account</h2>
			</div>
			
			
				<?php
				// List the users accounts
				if (!array_key_exists('error', $account_list['response']) && is_array($account_list['response']['accounts'])){
					foreach($account_list['response']['accounts'] as $account){
						if ($account['level'] != 'view'){
							echo '<div class="row-fluid">
							<div class="span1">
							<input type="checkbox" value="' . h($account['account_id']) . '" name="from_account[]" id="acct-' . h($account['screen_name']) . '">
							</div>
				<div class="span3">
				<label for="acct-' . h($account['screen_name']) . '"><img src="' . h($account['profile_image_url_https']) . '" width="48" height="48" alt="' . h($account['screen_name']) . '" /></label>
				</div>
				<div class="span8">
				' . h($account['name']) . '
				<br /><a href="http://twitter.com/' . h($account['screen_name']) . '">@' . h($account['screen_name']) . '</a>
				</div>
			</div>';
						}
					}
				}
				?>
		</div>
	</div>
	</form>
</div>

<?php
	include_once(ROOT. '/_footer.php');
?>