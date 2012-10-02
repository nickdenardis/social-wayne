<?php
	define('ROOT', dirname(__FILE__));
	include_once(ROOT . '/lib/define.php');
	
	$page_title = 'New URL';
	
	include_once(ROOT . '/_header.php');
?>
<div class="row-fluid">
	<div class="sidebar-nav">
		<div class="well span3">
			<ul class="nav nav-list"> 
			  <li class="nav-header">Admin Menu</li>        
			  <li><a href="<?php echo PATH; ?>"><i class="icon-home"></i> Dashboard</a></li>
	          <li><a href="#"><i class="icon-envelope"></i> Messages <span class="badge badge-info">4</span></a></li>
	          <li><a href="#"><i class="icon-comment"></i> Comments <span class="badge badge-info">10</span></a></li>
			  <li class="active"><a href="#"><i class="icon-user"></i> Members</a></li>
	          <li class="divider"></li>
			  <li><a href="#"><i class="icon-comment"></i> Settings</a></li>
			  <li><a href="?logout"><i class="icon-share"></i> Logout</a></li>
			</ul>
		</div>
	</div>
	
	<div class="span6">
		<h1>Add New URL</h1>
		
		
	</div>
</div>

<?php
	include_once(ROOT. '/_footer.php');
?>