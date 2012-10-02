<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title><?php echo h($page_title); ?> - Social Dashboard - Wayne State University</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <link rel="stylesheet" href="<?php echo PATH; ?>css/bootstrap.min.css">
        <style>
            body {
                padding-top: 60px;
                padding-bottom: 40px;
            }
        </style>
        <link rel="stylesheet" href="<?php echo PATH; ?>css/bootstrap-responsive.min.css">
        <link rel="stylesheet" href="<?php echo PATH; ?>css/main.css">

        <script src="<?php echo PATH; ?>js/vendor/modernizr-2.6.1-respond-1.1.0.min.js"></script>
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an outdated browser. <a href="http://browsehappy.com/">Upgrade your browser today</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to better experience this site.</p>
        <![endif]-->

        <div class="navbar navbar-inverse navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <a class="brand" href="<?php echo PATH; ?>">Social Dashboard</a>
                    <div class="nav-collapse collapse">
                        <ul class="nav">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">New <b class="caret"></b></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?php echo PATH; ?>message/twitter/new">Tweet</a></li>
                                    <li><a href="<?php echo PATH; ?>message/facebook/new">FB Update</a></li>
                                    <li><a href="<?php echo PATH; ?>message/tumblr/new">Tumblr</a></li>
                                    <li><a href="<?php echo PATH; ?>url/new">URL</a></li>
                                </ul>
                            </li>
                            
                            <li><a href="<?php echo PATH; ?>mentions/listing">Mentions</a></li>
                            <li><a href="<?php echo PATH; ?>stats/listing">Stats</a></li>
                        </ul>
                        <?php if (!isset($_SESSION['sessionid'])){ ?>
                        <form class="navbar-form pull-right" method="post">
                            <input class="span2" type="text" placeholder="AccessID" name="accessid" value="<?php echo (isset($_POST['accessid'])?$_POST['accessid']:''); ?>">
                            <input class="span2" type="password" placeholder="Password" name="password">
                            <button type="submit" class="btn">Sign in</button>
                        </form>
                        <?php }else{ ?>
                            <ul class="nav pull-right">
                                <li><a href="?logout">(<?php echo h($_SESSION['user_details']['accessid']); ?>) Logout <i class="icon-white icon-off"></i></a></li>
                            </ul>
                        <?php } ?>
                    </div><!--/.nav-collapse -->
                </div>
            </div>
        </div>

        <div class="container">
        	<?php echo Flash(); ?>
