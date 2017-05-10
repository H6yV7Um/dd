<?php
/*!
* HybridAuth
* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
* (c) 2009-2012, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html 
*/

/** 
 * Yahoo OAuth Class
 * 
 * @package             HybridAuth providers package 
 * @author              Lukasz Koprowski <azram19@gmail.com>
 * @version             0.2
 * @license             BSD License
 */ 

/**
 * Hybrid_Providers_Yahoo - Yahoo provider adapter based on OAuth1 protocol
 */
require LIB_PATH."/Hybrid/Provider_Model_OAuth2YJP.php";
class Hybrid_Providers_YahooJapan extends Hybrid_Provider_Model_OAuth2YJP
{
	function initialize() 
	{
		parent::initialize();

		// Provider api end-points
		$this->api->api_base_url  = 'https://userinfo.yahooapis.jp/yconnect/v1/';
		$this->api->authorize_url = 'https://auth.login.yahoo.co.jp/yconnect/v1/authorization';
		$this->api->token_url     = 'https://auth.login.yahoo.co.jp/yconnect/v1/token';

		$this->api->curl_authenticate_method  = "GET";
	}

	/**
	 * grab the user profile from the api client
	 */
	function getUserProfile()
	{
		$data = $this->api->get( "attribute" );

		if ( ! isset( $data['user_id'] ) ){
			throw new Exception( "User profile request failed! {$this->providerId} returned an invalide response.", 6 );
		}

		$this->user->profile->identifier    = (array_key_exists('user_id',$data))?$data['user_id']:"";
		$this->user->profile->firstName     = (array_key_exists('given_name',$data))?$data['given_name']:"";
		$this->user->profile->lastName      = (array_key_exists('family_name',$data))?$data['family_name']:"";
		$this->user->profile->displayName   = (array_key_exists('name',$data))?$data['name']:"";
		$this->user->profile->gender        = (array_key_exists('gender',$data))?$data['gender']:"";

		//wl.basic
		$this->user->profile->profileURL    = (array_key_exists($data,'link'))?$data->link:"";

		//wl.emails
		$this->user->profile->email         = (array_key_exists('email',$data))?$data['email']:"";
		$this->user->profile->emailVerified = (array_key_exists('email_verified',$data))?$data['email_verified']:"";

		//wl.birthday
		$this->user->profile->birthDay      = (array_key_exists('birthday',$data))?$data['birthday']:"";

		//echo json_encode($data);exit();
		return $this->user->profile;
	}


	/**
	 * load the current logged in user contacts list from the IDp api client
	 */

	/* Windows Live api does not support retrieval of email addresses (only hashes :/) */
	function getUserContacts()
	{
		$response = $this->api->get( 'me/contacts' );

		if ( $this->api->http_code != 200 )
		{
			throw new Exception( 'User contacts request failed! ' . $this->providerId . ' returned an error: ' . $this->errorMessageByStatus( $this->api->http_code ) );
		}

		if ( ! $response->data && ( $response->error != 0 ) )
		{
			return array();
		}

		$contacts = array();

		foreach( $response->data as $item ) {
			$uc = new Hybrid_User_Contact();

			$uc->identifier   = (property_exists($item,'id'))?$item->id:"";
			$uc->displayName  = (property_exists($item,'name'))?$item->name:"";

			$contacts[] = $uc;
		}

		return $contacts;
	}
}
