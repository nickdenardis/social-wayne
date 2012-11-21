<?php
	define('ROOT', dirname(__FILE__));
	include_once(ROOT . '/lib/define.php');
	
	// oAuth stuff
	include_once(ROOT . '/lib/themattharris/tmhOAuth.php');
	include_once(ROOT . '/lib/themattharris/tmhUtilities.php');
	
	// Page stuff
	$page_title = 'Stats';
	$page_url = $_SERVER['PHP_SELF'];
	
	// Get a list of all the account this user has access to
	$stats_response = $c->sendRequest('go/url/listing', array('utm_campaign' => 'social'), 'get');
	
	include_once(ROOT . '/_header.php');
?>	
<div class="row-fluid" id="content">
	<form accept-charset="UTF-8" action="" method="post">
	<div class="span12">
		<div class="list-view">
			<div class="list-header well">
				<h2>Recent URL's</h2>
			</div>
			<?php Pre($stats_response['response']['urls']); ?>
		</div>
	</div>
	</form>
</div>

<?php
	include_once(ROOT. '/_footer.php');
?>