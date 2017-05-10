<?php
/*!
* HybridAuth
* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
* (c) 2009-2012, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html 
*/

/**
 * To implement an OAuth 1 based service provider, Hybrid_Provider_Model_OAuth1Kaskus
 * can be used to save the hassle of the authentication flow. 
 * 
 * Each class that inherit from Hybrid_Provider_Model_OAuth1Kaskus have to implemenent
 * at least 2 methods:
 *   Hybrid_Providers_{provider_name}::initialize()     to setup the provider api end-points urls
 *   Hybrid_Providers_{provider_name}::getUserProfile() to grab the user profile
 *
 * Hybrid_Provider_Model_OAuth1Kaskus use OAuth1Client v0.1 which can be found on
 * Hybrid/thirdparty/OAuth/OAuth1Client.php
 */
class Hybrid_Provider_Model_OAuth1Kaskus extends Hybrid_Provider_Model
{
	public $request_tokens_raw = null; // request_tokens as recived from provider
	public $access_tokens_raw  = null; // access_tokens as recived from provider
	
    /**
	 * try to get the error message from provider api
	 * @param string $code
	 * @return string
	 */
	function errorMessageByStatus( $code = null ) { 
		$http_status_codes = ARRAY(
			200 => "OK: Success!",
			304 => "Not Modified: There was no new data to return.",
			400 => "Bad Request: The request was invalid.",
			401 => "Unauthorized.",
			403 => "Forbidden: The request is understood, but it has been refused.",
			404 => "Not Found: The URI requested is invalid or the resource requested does not exists.",
			406 => "Not Acceptable.", 
			500 => "Internal Server Error: Something is broken.",
			502 => "Bad Gateway.",
			503 => "Service Unavailable.",
		);

		if( ! $code && $this->api ) {
		    $code = $this->api->http_code;
		}
			

		if( isset( $http_status_codes[ $code ] ) ){
		    return $code . " " . $http_status_codes[ $code ];
		}
			
	}

	// --------------------------------------------------------------------

   /**
    * @param
    * @return
	* adapter initializer 
	*/
	function initialize()
	{
		// 1 - check application credentials
		if ( ! $this->config["keys"]["key"] || ! $this->config["keys"]["secret"] ){
			throw new Exception( "Your application key and secret are required in order to connect to {$this->providerId}.", 4 );
		}

		// 2 - include OAuth lib and client
		require_once Hybrid_Auth::$config["path_libraries"] . "OAuth/OAuth.php";
		require_once Hybrid_Auth::$config["path_libraries"] . "OAuth/OAuth1Client.php";
		require_once Hybrid_Auth::$config["path_libraries"] . "OAuth/OAuth1ClientKas.php";

		// 3.1 - setup access_token if any stored
		if( $this->token( "access_token" ) ){
			$this->api = new OAuth1ClientKas( 
				$this->config["keys"]["key"], $this->config["keys"]["secret"],
				$this->token( "access_token" ), $this->token( "access_token_secret" ) 
			);
		}

		// 3.2 - setup request_token if any stored, in order to exchange with an access token
		elseif( $this->token( "request_token" ) ){
			$this->api = new OAuth1ClientKas( 
				$this->config["keys"]["key"], $this->config["keys"]["secret"], 
				$this->token( "request_token" ), $this->token( "request_token_secret" ) 
			);
		}

		// 3.3 - instanciate OAuth client with client credentials
		else{
			$this->api = new OAuth1ClientKas( $this->config["keys"]["key"], $this->config["keys"]["secret"] );
		}

		// Set curl proxy if exist
		if( isset( Hybrid_Auth::$config["proxy"] ) ){
			$this->api->curl_proxy = Hybrid_Auth::$config["proxy"];
		}
	}

	// --------------------------------------------------------------------

   /**
    * @param
    * @return
	* begin login step 
	*/
	function loginBegin()
	{
		$tokens = $this->api->requestToken( $this->endpoint ); 
        
		// request tokens as recived from provider
		$this->request_tokens_raw = $tokens;
		
		// check the last HTTP status code returned
		if ( $this->api->http_code != 200 ){
			throw new Exception( "Authentication failed! {$this->providerId} returned an error. " . $this->errorMessageByStatus( $this->api->http_code ), 5 );
		}

		if ( ! isset( $tokens["oauth_token"] ) ){
			throw new Exception( "Authentication failed! {$this->providerId} returned an invalid oauth token.", 5 );
		}

		$this->token( "request_token"       , $tokens["oauth_token"] ); 
		$this->token( "request_token_secret", $tokens["oauth_token_secret"] ); 
		
		Hybrid_Logger::debug(" session after requestToken : " . var_export($_SESSION,true));
		//redirect the user to the provider authentication url
		$extra = array('token'=>$tokens["oauth_token"]);
		Hybrid_Auth::redirect( $this->api->authorizeUrl( $tokens,$extra));
	}

	// --------------------------------------------------------------------

   /**
    * @param
    * @return
	* finish login step 
	*/ 
	function loginFinish()
	{
	    //Kaskus 这里不标准，将oauth_token 用作verfier
		$oauth_token    = (array_key_exists('oauth_token',$_REQUEST))?$_REQUEST['oauth_token']:'';
		if(!$oauth_token){
			$oauth_token    = (array_key_exists('token',$_REQUEST))?$_REQUEST['token']:'';
		}
		//$oauth_verifier = (array_key_exists('oauth_verifier',$_REQUEST))?$_REQUEST['oauth_verifier']:"";
        $oauth_verifier = $oauth_token;
		
		if ( ! $oauth_token || ! $oauth_verifier ){
			throw new Exception( "Authentication failed! {$this->providerId} returned an invalid oauth verifier.", 5 );
		}

		// request an access token
		//Error
		//$accessToken = [
		//    'access' => 'DENIED',
		//    'message' => '<Error Message>'
		//];
		// Success
		//$accessToken =
		//    [
		//        'access' => 'GRANTED',
		//        'oauth_token' => '<token key>',
	    //        'oauth_token_secret' => '<token secret>',
		//        'userid' => '<user id>',
		//        'username' => '<user name>',
		//        'expired' => '<expired time>',
		//    ];
		$tokens = $this->api->accessToken( $oauth_verifier );
		Hybrid_Logger::debug(" {$this->providerId}  access token is： " . json_encode($tokens));;
		
		// access tokens as recived from provider
		$this->access_tokens_raw = $tokens;

		// check the last HTTP status code returned
		if ( $this->api->http_code != 200 ){
			throw new Exception( "Authentication failed! {$this->providerId} returned an error. " . $this->errorMessageByStatus( $this->api->http_code ), 5 );
		}

		// we should have an access_token, or else, something has gone wrong
		if ( ! isset( $tokens["oauth_token"] ) ){
			throw new Exception( "Authentication failed! {$this->providerId} returned an invalid access token.", 5 );
		}

		// Kaskus still need request token
		//$this->deleteToken( "request_token"        );
		//$this->deleteToken( "request_token_secret" );

		// sotre access_token for later user
		$this->token( "raw_access_token"    , $tokens);
		$this->token( "access_token"        , $tokens['oauth_token'] );
		$this->token( "access_token_secret" , $tokens['oauth_token_secret'] ); 

		// set user as logged in to the current provider
		$this->setUserConnected(); 
	}
}
