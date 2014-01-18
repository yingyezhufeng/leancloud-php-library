<?php
include 'AVConfig.php';
include 'AVObject.php';
include 'AVQuery.php';
include 'AVUser.php';
include 'AVFile.php';
include 'AVPush.php';
include 'AVGeoPoint.php';
include 'AVACL.php';
include 'AVCloud.php';

class AVRestClient{

	private $_appid = '';
	private $_masterkey = '';
	private $_apikey = '';
	private $_AVurl = '';

	public $data;
	public $requestUrl = '';
	public $returnData = '';

	public function __construct(){
		$AVConfig = new AVConfig;
		$this->_appid = $AVConfig::APPID;
    	$this->_masterkey = $AVConfig::MASTERKEY;
    	$this->_apikey = $AVConfig::APIKEY;
    	$this->_AVurl = $AVConfig::AVOSCLOUDURL;

		if(empty($this->_appid) || empty($this->_apikey) || empty($this->_masterkey)){
			$this->throwError('You must set your Application ID, Master Key and Application API Key');
		}

		$version = curl_version();
		$ssl_supported = ( $version['features'] & CURL_VERSION_SSL );

		if(!$ssl_supported){
			$this->throwError('CURL ssl support not found');
		}

	}

	/*
	 * All requests go through this function
	 *
	 *
	 */
	public function request($args){
		$isFile = false;
		$c = curl_init();
		curl_setopt($c, CURLOPT_TIMEOUT, 30);
		curl_setopt($c, CURLOPT_USERAGENT, 'AVOSCloud.com-php-library/2.0');
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLINFO_HEADER_OUT, true);
		if(substr($args['requestUrl'],0,5) == 'files'){
			curl_setopt($c, CURLOPT_HTTPHEADER, array(
				'Content-Type: '.$args['contentType'],
				'X-AVOSCloud-Application-Id: '.$this->_appid,
				'X-AVOSCloud-Master-Key: '.$this->_masterkey
			));
			$isFile = true;
		}
		else if(substr($args['requestUrl'],0,5) == 'users' && isset($args['sessionToken'])){
			curl_setopt($c, CURLOPT_HTTPHEADER, array(
    			'Content-Type: application/json',
    			'X-AVOSCloud-Application-Id: '.$this->_appid,
    			'X-AVOSCloud-Application-API-Key: '.$this->_apikey,
    			'X-AVOSCloud-Session-Token: '.$args['sessionToken']
    		));
		}
		else{
			curl_setopt($c, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'X-AVOSCloud-Application-Id: '.$this->_appid,
				'X-AVOSCloud-Application-Key: '.$this->_apikey,
				'X-AVOSCloud-Master-Key: '.$this->_masterkey
			));
		}
		curl_setopt($c, CURLOPT_CUSTOMREQUEST, $args['method']);
		$url = $this->_AVurl . $args['requestUrl'];

		if($args['method'] == 'PUT' || $args['method'] == 'POST'){
			if($isFile){
				$postData = $args['data'];
			}
			else{
				$postData = json_encode($args['data']);
			}

			curl_setopt($c, CURLOPT_POSTFIELDS, $postData );
		}

		if($args['requestUrl'] == 'login'){
			$urlParams = http_build_query($args['data'], '', '&');
			$url = $url.'?'.$urlParams;
		}
		if(array_key_exists('urlParams',$args)){
			$urlParams = http_build_query($args['urlParams'], '', '&');
    		$url = $url.'?'.$urlParams;
		}

		curl_setopt($c, CURLOPT_URL, $url);

		$response = curl_exec($c);
		$responseCode = curl_getinfo($c, CURLINFO_HTTP_CODE);

		$expectedCode = array('200');
		if($args['method'] == 'POST' && substr($args['requestUrl'],0,4) != 'push'){
			// checking if it is not cloud code - it returns code 200
			if(substr($args['requestUrl'],0,9) != 'functions'){
				$expectedCode = array('200','201');
			}
		}

		//BELOW HELPS WITH DEBUGGING
		/*
		if(!in_array($responseCode,$expectedCode)){
			//print_r($response);
			//print_r($args);
		}
		*/
		return $this->checkResponse($response,$responseCode,$expectedCode);
	}

	public function dataType($type,$params){
		if($type != ''){
			switch($type){
				case 'date':
					$return = array(
						"__type" => "Date",
						"iso" => date("c", strtotime($params))
					);
					break;
				case 'bytes':
					$return = array(
						"__type" => "Bytes",
						"base64" => base64_encode($params)
					);
					break;
				case 'pointer':
					$return = array(
						"__type" => "Pointer",
						"className" => $params[0],
						"objectId" => $params[1]
					);
					break;
				case 'geopoint':
					$return = array(
						"__type" => "GeoPoint",
						"latitude" => floatval($params[0]),
						"longitude" => floatval($params[1])
					);
					break;
				case 'file':
					$return = array(
						"__type" => "File",
						"name" => $params[0],
					);
					break;
				case 'increment':
					$return = array(
						"__op" => "Increment",
						"amount" => $params[0]
					);
					break;
				case 'decrement':
					$return = array(
						"__op" => "Decrement",
						"amount" => $params[0]
					);
					break;
				default:
					$return = false;
					break;
			}

			return $return;
		}
	}

	public function throwError($msg,$code=0){
		throw new AVLibraryException($msg,$code);
	}


	function printStackTrace() {
    $stack = debug_backtrace();
    $output = 'Stack trace:' . PHP_EOL;

    $stackLen = count($stack);
    for ($i = 1; $i < $stackLen; $i++) {
        $entry = $stack[$i];

        $func = $entry['function'] . '(';
        $argsLen = count($entry['args']);
        for ($j = 0; $j < $argsLen; $j++) {
            $func .= $entry['args'][$j];
            if ($j < $argsLen - 1) $func .= ', ';
        }
        $func .= ')';

        $output .= '#' . ($i - 1) . ' ' . $entry['file'] . ':' . $entry['line'] . ' - ' . $func . PHP_EOL;
    }

	print_r($output);
}

	private function checkResponse($response,$responseCode,$expectedCode){
		//TODO: Need to also check for response for a correct result from AVOSCloud.com
		if(!in_array($responseCode,$expectedCode)){
			$error = json_decode($response);
			$this->printStackTrace();
			$this->throwError($error->error,$error->code);
		}
		else{
			//check for empty return
			if($response == '{}'){
				return true;
			}
			else{
				return json_decode($response);
			}
		}
	}
}


class AVLibraryException extends Exception{
	public function __construct($message, $code = 0, Exception $previous = null) {
		//codes are only set by a AVOSCloud.com error
		if($code != 0){
			$message = "AVOSCloud.com error: ".$message;
		}

		parent::__construct($message, $code, $previous);
	}

	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}

}

?>
