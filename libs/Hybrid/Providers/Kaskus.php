<?php
/***************************************************************************
 * 
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
/**
 * Kaskus OAuth Class
 * 
 * @file libs/Hybrid/Providers/Kaskus.php
 * @author liuzhenhua03(com@baidu.com)
 * @date 2015/07/15 20:52:51
 * @brief 
 *  
 **/

/**
 * Hybrid_Providers_Kaskus - Kaskus provider adapter based on OAuth1 protocol
 */
require_once LIB_PATH."/Hybrid/Provider_Model_OAuth1Kaskus.php";
class Hybrid_Providers_Kaskus extends Hybrid_Provider_Model_OAuth1Kaskus
{
    function initialize()
    {
        parent::initialize();

        // Provider api end-points
        $this->api->api_base_url      = 'https://www.kaskus.co.id/api/oauth';
        $this->api->authorize_url     = 'https://www.kaskus.co.id/api/oauth/authorize';
        $this->api->request_token_url = 'https://www.kaskus.co.id/api/oauth/token';
        $this->api->access_token_url  = 'https://www.kaskus.co.id/api/oauth/accesstoken';
    }

    /**
     * 
     * @see Hybrid_Provider_Model::getUserProfile()
     * @return object 
     */
    function getUserProfile()
    {
        $raw_access_token         = $this->token( "raw_access_token");

        //get User basic info from $raw_access_token 
        if ( empty( $raw_access_token ) ){
            throw new Exception( "User profile request failed! {$this->providerId}  has an empty raw_access_token.", 6 );
        }

        $data = $raw_access_token;
        
        $this->user->profile->identifier    = isset($data['userid']) ? $data['userid']: "";
        $this->user->profile->firstName     = isset($data['username']) ? $data['username']: "";
        $this->user->profile->lastName      = isset($data['lastName']) ? $data['lastName']: "";
        $this->user->profile->displayName   = isset($data['username']) ? $data['username']: "";
        $this->user->profile->profileURL    = isset($data['profileURL']) ? $data['profileURL']: "";
        $this->user->profile->gender        = isset($data['gender']) ? $data['gender']: "";

        if( $this->user->profile->gender == "F" ){
            $this->user->profile->gender = "female";
        }

        if( $this->user->profile->gender == "M" ){
            $this->user->profile->gender = "male";
        }

        if( isset($data['email']) ){
            $this->user->profile->email         = $data['email'];
            $this->user->profile->emailVerified = $data['email'];
        }

        $this->user->profile->age           = isset($data['age']) ? $data['age']: "";
        $this->user->profile->photoURL      = isset($data['photoURL']) ? $data['photoURL']: "";

        $this->user->profile->address       = isset($data['address']) ? $data['address']: "";
        $this->user->profile->language      = isset($data['language']) ? $data['language']: "";
    
        return $this->user->profile;
    }

    /**
     * load the user contacts
     * @see Hybrid_Provider_Model::getUserContacts()
     * @return object 
     */
    function getUserContacts()
    {
        $userId = $this->getCurrentUserId();

        $parameters = array();
        $parameters['format']	= 'json';
        $parameters['count'] = 'max';

        $response = $this->api->get('user/' . $userId . '/contacts', $parameters);

        if ( $this->api->http_code != 200 )
        {
            throw new Exception( 'User contacts request failed! ' . $this->providerId . ' returned an error: ' . $this->errorMessageByStatus( $this->api->http_code ) );
        }

        if ( !$response->contacts->contact && ( $response->errcode != 0 ) )
        {
            return array();
        }

        $contacts = array();

        foreach( $response->contacts->contact as $item ) {
            $uc = new Hybrid_User_Contact();

            $uc->identifier   = $this->selectGUID( $item );
            $uc->email        = $this->selectEmail( $item->fields );
            $uc->displayName  = $this->selectName( $item->fields );
            $uc->photoURL     = $this->selectPhoto( $item->fields );

            $contacts[] = $uc;
        }

        return $contacts;
    }

    /**
     * 取用户验证信息
     * @param
     * @return
     */
    public function getUserAuthInfo($userId){
        $parameters = array();
        $parameters['format'] = 'json';
        $parameters['count']  = 'max';
	//var_dump($userId);
        $response = $this->api->get('/user/'.$userId,array(),false);
        if ( $this->api->http_code != 200 )
        {   
		Bingo_Log::warning("kaskus获取到用户信息错误 http_code:{$this->api->http_code}", 'dal');
		//throw new Exception( 'User contacts request failed! ' . $this->providerId . ' returned an error: ' . $this->errorMessageByStatus( $this->api->http_code ) );
        }

        return $response;
    }


    /**
     * return the user activity stream
     * @param objcet $stream
     * @return  object
     */
    function getUserActivity( $stream )
    {
        $userId = $this->getCurrentUserId();

        $parameters = array();
        $parameters['format']	= 'json';
        $parameters['count']	= 'max';

        $response = $this->api->get('user/' . $userId . '/updates', $parameters);

        if( ! $response->updates || $this->api->http_code != 200 )
        {
            throw new Exception( 'User activity request failed! ' . $this->providerId . ' returned an error: ' . $this->errorMessageByStatus( $this->api->http_code ) );
        }

        $activities = array();

        foreach( $response->updates as $item ){
            $ua = new Hybrid_User_Activity();

            $ua->id = (property_exists($item,'collectionID'))?$item->collectionID:"";
            $ua->date = (property_exists($item,'lastUpdated'))?$item->lastUpdated:"";
            $ua->text = (property_exists($item,'loc_longForm'))?$item->loc_longForm:"";

            $ua->user->identifier  = (property_exists($item,'profile_guid'))?$item->profile_guid:"";
            $ua->user->displayName = (property_exists($item,'profile_nickname'))?$item->profile_nickname:"";
            $ua->user->profileURL  = (property_exists($item,'profile_profileUrl'))?$item->profile_profileUrl:"";
            $ua->user->photoURL    = (property_exists($item,'profile_displayImage'))?$item->profile_displayImage:"";

            $activities[] = $ua;
        }

        if( $stream == "me" ){
            $userId = $this->getCurrentUserId();
            $my_activities = array();

            foreach( $activities as $a ){
                if( $a->user->identifier == $userId ){
                    $my_activities[] = $a;
                }
            }

            return $my_activities;
        }

        return $activities;
    }


    /**
     * 
     * @param unknown $vs
     * @param unknown $t
     * @return unknown|NULL
     */
    function select($vs, $t)
    {
        foreach( $vs as $v ){
            if( $v->type == $t ) {
                return $v;
            }
        }

        return null;
    }

    /**
     * 
     * @param unknown $v
     * @return string
     */
    function selectGUID( $v )
    {
        return (property_exists($v,'id'))?$v->id:"";
    }

    /**
     * 
     * @param unknown $v
     * @return string
     */
    function selectName( $v )
    {
        $s = $this->select($v, 'name');

        if( ! $s ){
            $s = $this->select($v, 'nickname');
            return ($s)?$s->value:"";
        } else {
            return ($s)?$s->value->givenName . " " . $s->value->familyName:"";
        }
    }

    /**
     * 
     * @param unknown $v
     * @return Ambigous <string, unknown, NULL>
     */
    function selectNickame( $v )
    {
        $s = $this->select($v, 'nickname');
        return ($s)?$s:"";
    }

    /**
     * 
     * @param unknown $v
     * @return Ambigous <string, boolean>
     */
    function selectPhoto( $v )
    {
        $s = $this->select($v, 'guid');
        return ($s)?(property_exists($s,'image')):"";
    }

    /**
     * 
     * @param unknown $v
     * @return string
     */
    function selectEmail( $v )
    {
        $s = $this->select($v, 'email');
        return ($s)?$s->value:"";
    }

    /**
     * 
     * @throws Exception
     * @return string
     */
    public function getCurrentUserId()
    {
        $parameters = array();
        $parameters['format']	= 'json';

        $response = $this->api->get( 'me/guid', $parameters );

        if ( ! isset( $response->guid->value ) ){
            throw new Exception( "User id request failed! {$this->providerId} returned an invalid response." );
        }

        return $response->guid->value;
    }
}







/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
