<?php
/*
	#######################################################
	#  CMS API / PHP Wrapper
	#  By: Nick DeNardis | ndenardis@wayne.edu
	#
	#  Docs: http://api.wayne.edu/
	#
	#  License(s): licensed under:
    #  GPL (GPL-LICENSE.txt)
    # 
    #  Third-party code:
    #  XML Library by Keith Devens
	#  xmlparser.php
	#
	#  Version 0.1
	########################################################
*/

include_once('xmlparser.php');

class Phpcms {
	var $apiKey;  // To obtain an API key: http://api.wayne.edu/
	var $cmsREST = 'http://api.wayne.edu/v1/'; // REST URL Version 1.0
	var $cmsRESTSSL = 'https://api.wayne.edu/v1/'; // Secure REST URL Version 1.0
	var $parser = 'json'; // Use the included XML parser? Default: true.
	var $debug = false; // Switch for debug mode
	var $sessionid;
	var $same_server = false;
	
	function __construct($apiKey=false, $mode='production') {
		if($apiKey)
			$this->apiKey = $apiKey;
			
		//$mode = (strstr($_SERVER['SERVER_NAME'],'www-dev') === false)?'production':'dev';
		
		if ($mode == 'dev'){
			// Use the local server paths for now
			$this->cmsREST = 'http://www-dev.api.wayne.edu/v1/'; 
			$this->cmsRESTSSL = 'https://www-dev.api.wayne.edu/v1/';
		}
		
		// Check to see if we are on the same server as the API classes
		
	}
	
	/**
	 * cms.api.getapiinfo
	 * 
	 * @param null
	 * @return version => string
	 * @link http://api.wayne.edu/docs/?method=cms.apikey.getkeyinfo
	 */
	function api_getinfo() {
		return $this->sendRequest('cms.api.info');
	}
	
	/**
	 * setSession
	 * 
	 * @param session => string
	 * @return bool
	 */
	function setSession($sessionid) {
		return ($this->sessionid = $sessionid);
	}
	
	/**
	 * buildArguments
	 *
	 * @param $p(array)
	 * @return string
	 */
	function buildArguments($p) {
		$args = '';
		foreach ($p as $key => $value) {
			// Don't include these
			if ($key == 'method' || $key == 'submit' || $key == 'MAX_FILE_SIZE') continue;
		
			$args .= $key.'='.urlencode($value).'&';
		} 
		
		// Chop off last ampersand
		return substr($args, 0, -1);
	}
	
	/**
	 * sendRequest
	 *
	 * @param $method(string), $args(array), $postmethod(string / post,get)
	 * @return array or xml
	 */
	function sendRequest($method=null,$args=null,$postmethod='get',$use_ssl=false) {
		// Convert array to string
		$reqURL = (($use_ssl)?$this->cmsRESTSSL:$this->cmsREST).'?api_key='.$this->apiKey.'&return=json&method='.$method;
		
		// If there is a session, pass the info along
		if ($this->sessionid != '')
			$args['sessionid'] = (string)urlencode($this->sessionid);

		if ($postmethod == 'get') {
			if (is_array($args)) {
				$getArgs = $this->buildArguments($args);
			}else{
				$getArgs = $args;
			}
			
			$reqURL .= '&'.$getArgs;
		}
		
		if ($postmethod == 'post' && !empty($args['sessionid']))
			$reqURL .= '&sessionid='. $args['sessionid'];
		
		$curl_handle = curl_init();
		
		curl_setopt ($curl_handle, CURLOPT_URL, $reqURL);
		curl_setopt ($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($curl_handle, CURLOPT_CONNECTTIMEOUT, 1);
		curl_setopt ($curl_handle, CURLOPT_HEADER, 0);
		curl_setopt ($curl_handle, CURLOPT_TIMEOUT, 0);
		curl_setopt ($curl_handle, CURLOPT_SSLVERSION, 3);
		curl_setopt ($curl_handle, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); 
		curl_setopt ($curl_handle, CURLOPT_REFERER, 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); 
		
		// Set the custom HTTP Headers
		$http_header = array();
		$http_header[] = 'X-Api-Key: ' . $this->apiKey;
		$http_header[] = 'X-Return: json';
		if (isset($args['sessionid']))
			$http_header[] = 'X-Sessionid: ' . $args['sessionid'];
		
		curl_setopt ($curl_handle, CURLOPT_HTTPHEADER, $http_header);
		
		if ($postmethod == 'post') {
			curl_setopt($curl_handle, CURLOPT_POST, 1);
			if ($method == 'cms.file.upload') {
				curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $args);
			}else{
				curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $this->buildArguments($args));
			}
		}
		
		// Debug?
		if ($this->debug) {
			echo '<pre>';
			print_r($reqURL);
			print_r($http_header);
			print_r($this->buildArguments($args));
			echo '</pre>';
		}	
		
		
		$response = curl_exec($curl_handle);
		
		if (!$response)
			$response = curl_error($curl_handle);
		
		curl_close($curl_handle);
		
		// Debug?
		if ($this->debug) {
			echo '<pre>';
			print_r($response);
			echo '</pre>';
		}		
		
		// Return array or XML
		if ($this->parser == 'xml') {
			return XML_unserialize($response);
		} else if ($this->parser == 'json') {
			return json_decode($response, true);
		} else {
			return $response;
		}
		
	}
}

class Php5cms extends Phpcms {  
	public function sendRequest($method=null,$args=null,$postmethod='get',$tryagain=true) {    
		try{
			$result = parent::sendRequest($method, $args, $postmethod);
			
			if ($tryagain && is_null($result)){
				$result = parent::sendRequest($method, $args, $postmethod, false);
			}elseif (is_null($result)){
				throw new CMSException("No response", $method, 8888, 'n/a');
			}
			
			if (is_array($result) && isset($result['error']) && $result['error']){
				throw new CMSException($result['error']['message'], $method, $result['error']['code'], $result['error']['field']);
			}
		}catch (CMSException $e) {}
		
		if (isset($result['response'])){
			return $result['response'];
		}
		
		return $result;
	}
}

class CMSException extends Exception {
	var $details;
	var $method;
	
	public function __construct($message, $method, $code=0, $details='') {
		$this->details = $details;
		$this->method = $method;
		parent::__construct($message, $code);
	}
	
	public function getDetails() {
		return $this->details;
	}
	
	public function __toString() {
		return "{$this->method} exception [{$this->code}]: {$this->getMessage()} ({$this->details})\n";
	}
}
?>