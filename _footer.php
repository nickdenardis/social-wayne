<hr>
            <footer>
                <p>&copy; Wayne State University <?php echo date('Y') . ' '; echo (MODE == 'production')?'<span class="label label-success"><a href="' . PATH . '?api=flop">Production API</a></span>':'<span class="label label-important"><a href="' . PATH . '?api=flop">Dev API</a></span>'; ?> </p>
            </footer>

        </div> <!-- /container -->

        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="<?php echo PATH; ?>vendor/jquery/jquery-2.0.2.min.js"><\/script>')</script>

        <script src="<?php echo PATH; ?>vendor/bootstrap/js/bootstrap.min.js"></script>
        
        <?php
        if (isset($page_js) && is_array($page_js)){
	        foreach($page_js as $file){
		        echo '<script src="' . $file . '"></script>';
	        }
        }
        ?>

        <script src="<?php echo PATH; ?>js/plugins.js"></script>
        <script src="<?php echo PATH; ?>js/main.js"></script>

        <script>
            var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
            (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
            g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
            s.parentNode.insertBefore(g,s)}(document,'script'));
        </script>
    </body>
</html>
