<?php
	define('ROOT', dirname(__FILE__));
	include_once(ROOT . '/lib/define.php');
	
	// oAuth stuff
	include_once(ROOT . '/lib/themattharris/tmhOAuth.php');
	include_once(ROOT . '/lib/themattharris/tmhUtilities.php');
	
	// Page stuff
	$page_title = 'New Tweet';
	$page_url = $_SERVER['PHP_SELF'];
		
	// Get a list of all the account this user has access to
	$account_list = $c->sendRequest('socialy/account/access', array(), 'get');
	
	include_once(ROOT . '/_header.php');
?>	
<div class="row-fluid" id="content">
	<div class="span9">
		<div class="list-view">
			<div class="list-header well">
				<h2>Add Tweet</h2>
			</div>
			    <form accept-charset="UTF-8" action="" method="post">
			        <textarea class="span10" id="new_message" name="new_message" placeholder="Type in your message" rows="5"></textarea>
			        <h6>320 characters remaining</h6>
			        <button class="btn btn-info" type="submit">Post New Message</button>
			    </form>
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
						echo '<li>';
						echo '<img src="' . h($account['profile_image_url_https']) . '" width="48" height="48" alt="' . h($account['screen_name']) . '" /><br />';
						echo h($account['name']) . '<br /><a href="http://twitter.com/' . h($account['screen_name']) . '">@' . h($account['screen_name']) . '</a>';
						echo '</li>';
					}
				}else{
				?>
				<li><a href="<?php echo PATH; ?>accounts/listing?start=1"><img src="<?php echo PATH; ?>img/twitter_signin.png" alt="twitter_signin" width="150" height="22" /></a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
</div>

<?php
	include_once(ROOT. '/_footer.php');
?>