<?php
	define('ROOT', dirname(__FILE__));
	include_once(ROOT . '/lib/define.php');
	
	$page_title = 'New URL';
	$page_url = $_SERVER['PHP_SELF'];
	
	// If creating a new URL
	if (isset($_POST['submit'])){
		// Make sure this is a valid URL
		$url_valid = false;
		
		$url_params = $_POST;
		
		if (substr(trim($_POST['url']), 0, 4) == 'http'){
			// Use CURL
			include_once(ROOT . '/lib/curl/curl.php');
			$myCurl = new cURL;
			
			// Grab the homepage to get all the info about it
			$page = $myCurl->get(trim($_POST['url']), NULL, true);
			
			 // If the page is returned successfully
	        if ($page['info']['http_code'] == '200'){
				$title = 'Page: ' . $page['info']['url'];
			
				// Include the DOM functions
				include_once(ROOT . '/lib/dom/simple_html_dom.php');
				
				// Grab the page title
				$html = str_get_html($page['response']);
				if (is_object($html)){
					$title = trim($html->find('title', 0)->plaintext);
				}
			}
			
			// Setup the URL Params
			$url_params['url'] = $page['info']['url'];
			$url_params['title'] = $title;
			
			$url_valid = true;
		}
		
		if ($url_valid){
			//Pre($url_params);
			
			// Actually create the URL
			$url_response = $c->sendRequest('go/url/create', $url_params , 'post', true);
			
			// If returned successfully 
			if (isset($url_response['response']['url'])){
				Flash('URL Created: <a href="http://go.wayne.edu/' . $url_response['response']['url']['short_url'] . '" target="_blank">http://go.wayne.edu/' . $url_response['response']['url']['short_url'] . '</a>', 'success');
			}else{
				Flash('Something went wrong in the API. Please check with a systems administrator.', 'error');
			}
		}else{
			Flash('The URL you entered is not valid, please try again', 'error');
		}
	}
	
	// Get the list of social URL's
	
	include_once(ROOT . '/_header.php');
?>
<div class="row-fluid">
	<div class="sidebar-nav">
		<div class="span3">
			<ul class="nav nav-list"> 
			  <li class="nav-header">Social</li>        
			  <li class="active"><a href="<?php echo PATH; ?>"><i class="icon-home"></i> Home</a></li>
	          <li><a href="#"><i class="icon-envelope"></i> Messages <span class="badge badge-info">4</span></a></li>
	          <li><a href="#"><i class="icon-comment"></i> Comments <span class="badge badge-info">10</span></a></li>
			  <li><a href="#"><i class="icon-user"></i> Members</a></li>
	          <li class="divider"></li>
			  <li><a href="<?php echo PATH; ?>url/new"><i class="icon-share"></i> New URL</a></li>
			  <li><a href="#"><i class="icon-fire"></i> Stats</a></li>
			  <li><a href="?logout"><i class="icon-share"></i> Logout</a></li>
			</ul>
		</div>
	</div>
	
	<div class="span9">
		<form method="post">
		<fieldset>
			<legend>New URL</legend>
			<div class="controls">
			  <label>URL</label>
			  <input type="text" placeholder="http://..."  class="input-xxlarge" name="url">
			</div>
			<div class="controls controls-row">
			  <input class="span2" type="text" name="short_url" placeholder="short_url (auto)">
			  <input class="span2" type="text" name="utm_source" placeholder="utm_source" value="twitter">
			  <input class="span2" type="text" name="utm_medium" placeholder="utm_medium" value="go.wayne.edu">
			  <input class="span2" type="text" name="utm_campaign" placeholder="utm_campaign" value="social">
			</div>
			<input type="submit" class="btn" name="submit" value="Create" />
		</fieldset>
		</form>
	</div>
</div>

<?php
	include_once(ROOT. '/_footer.php');
?>