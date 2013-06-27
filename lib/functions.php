<?php
function Pre($data, $display=true){
	$out = '<pre>' . print_r($data, true) . '</pre>';
	
	if ($display)
		echo $out;
	else
		return $out;
		
	return true;
}

function Flash($message='', $type='success'){
	$out = '';
	
	// Set the flash
	if ($message != ''){
		$_SESSION['flash'][$type][] = $message;	
		
		return $out;
	}
	
	if (isset($_SESSION['flash']) && is_array($_SESSION['flash'])){		
		// Diplay the flash
		foreach (array_keys($_SESSION['flash']) as $key){
			foreach($_SESSION['flash'][$key] as $num=>$message){
				$out .= '<div class="alert alert-' . $key . '"><button type="button" class="close" data-dismiss="alert">×</button>';
				$out .= $message;
				$out .= '</div>' . "\n";
			}
		}
		
		unset($_SESSION['flash']);
	}
	
	return $out;
}

function h($string, $display=false){
	// Make the string Web safe
	$out = htmlspecialchars(stripslashes($string));

	// Return or echo
	if ($display)
		echo $out;
	else
		return $out;

	return true;
}

// OAuth functions
function php_self($dropqs=true) {
  $protocol = 'http';
  if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
    $protocol = 'https';
  } elseif (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == '443')) {
    $protocol = 'https';
  }

  $url = sprintf('%s://%s%s',
    $protocol,
    $_SERVER['SERVER_NAME'],
    $_SERVER['REQUEST_URI']
  );

  $parts = parse_url($url);

  $port = $_SERVER['SERVER_PORT'];
  $scheme = $parts['scheme'];
  $host = $parts['host'];
  $path = @$parts['path'];
  $qs   = @$parts['query'];

  $port or $port = ($scheme == 'https') ? '443' : '80';

  if (($scheme == 'https' && $port != '443')
      || ($scheme == 'http' && $port != '80')) {
    $host = "$host:$port";
  }
  $url = "$scheme://$host$path";
  if ( ! $dropqs)
    return "{$url}?{$qs}";
  else
    return $url;
}