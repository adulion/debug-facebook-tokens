<?php

class debug_facebook_tokens {

  protected $appId;
  protected $apiSecret;

	public function __construct($config){

		$this->setAppId($config['appId']);
   	 	$this->setApiSecret($config['secret']);


	}
	
	public function debug($access_token)
	{
		return $this->__access_token_http_call($access_token,$this->__get_app_access_token());
	}

	public function setAppId($appId) {
		$this->appId = $appId;
		return $this;
	}

	public function setApiSecret($apiSecret) {
		$this->apiSecret = $apiSecret;
		return $this;
	}

	private function __access_token_http_call($access_token, $app_access_token)
	{
		$url = "https://graph.facebook.com/debug_token?input_token=".$access_token."&access_token=" . $app_access_token;

		$result = $this->__get_url($url);

		return $result[0];
	}

	private function __get_url( $url,  $javascript_loop = 0, $timeout = 5 )
	{
	    $url = str_replace( "&amp;", "&", urldecode(trim($url)) );

	    $cookie = tempnam ("/tmp", "CURLCOOKIE");
	    $ch = curl_init();
	    curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
	    curl_setopt( $ch, CURLOPT_URL, $url );
	    curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
	    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
	    curl_setopt( $ch, CURLOPT_ENCODING, "" );
	    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	    curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
	    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
	    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
	    curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
	    curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
	    $content = curl_exec( $ch );
	    $response = curl_getinfo( $ch );
	    curl_close ( $ch );

	    if ($response['http_code'] == 301 || $response['http_code'] == 302)
	    {
	        ini_set("user_agent", "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");

	        if ( $headers = get_headers($response['url']) )
	        {
	            foreach( $headers as $value )
	            {
	                if ( substr( strtolower($value), 0, 9 ) == "location:" )
	                    return $this->__get_url( trim( substr( $value, 9, strlen($value) ) ) );
	            }
	        }
	    }

	    if (    ( preg_match("/>[[:space:]]+window\.location\.replace\('(.*)'\)/i", $content, $value) || preg_match("/>[[:space:]]+window\.location\=\"(.*)\"/i", $content, $value) ) &&
	            $javascript_loop < 5
	    )
	    {
	        return $this->__get_url( $value[1], $javascript_loop+1 );
	    }
	    else
	    {
	        return array( $content, $response );
	    }
	}


	

	private function __get_app_access_token()
	{	

		$client_id = $this->appId;
		$client_secret = $this->apiSecret;

		$url = "https://graph.facebook.com/oauth/access_token";
		$postString = "client_id=$client_id&client_secret=$client_secret&grant_type=client_credentials";

		//
		//grab the curl stuff
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_FAILONERROR, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_VERBOSE, true);
		curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0); 
		//curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $postString);
		
		$response = curl_exec($curl);

		echo $response;
		$error = curl_error($curl);
		
		curl_close ($curl);	
	

		if(empty($error)){

			return substr($response,13);
		}
		else
		{
			return false;
		}
	}

}