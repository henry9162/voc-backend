<?php

namespace App;
use GuzzleHttp\Client;

class ChatRegistrationApi
{
    var $client, $auth_isAdmin, $auth_user, $guzzleClient, $user_data;

    public function __construct($auth_isAdmin = null, $auth_user = null) 
    {
        $this->auth_isAdmin = $auth_isAdmin;

        $this->auth_user = $auth_user;

        $this->client = new Client(
            array( 
                'curl' => array( CURLOPT_SSL_VERIFYPEER => false, ),
                "headers" => [ 'content-type' => 'application/json'],
        ));
    }

    public function get_loggedin_user_details()
    {
        $app_url = 'http://nyscbetaapi.azurewebsites.net';
        $authId =  $this->auth_user; 

        try
        {
            $url = $app_url . "/api/Voc/validateChatId";
            $data = ["isAdmin" => $this->auth_isAdmin, "user" => $authId];   
            $response = $this->client->post($url, [ 
                "body" => json_encode( $data ) 
            ]);

            $results = json_decode((string) $response->getBody()->getContents(), true);

            $this->user_data = $results;
        }
        catch (RequestException $e){
            $response = $this->StatusCodeHandling($e);
            return $response;
        }
    }

    public function StatusCodeHandling($e)
    {
        if ($e->getResponse()->getStatusCode() == ‘400’)
        {
            $this->get_loggedin_user_details();
        } 
        elseif ($e->getResponse()->getStatusCode() == ‘422’)
        {
            $response = json_decode($e->getResponse()->getBody(true)->getContents());
            return $response;
        } 
        elseif ($e->getResponse()->getStatusCode() == ‘500’)
        {
            $response = json_decode($e->getResponse()->getBody(true)->getContents());
            return $response;
        } 
        elseif ($e->getResponse()->getStatusCode() == ‘401’)
        {
            $response = json_decode($e->getResponse()->getBody(true)->getContents());
            return $response;
        } 
        elseif ($e->getResponse()->getStatusCode() == ‘403’)
        {
            $response = json_decode($e->getResponse()->getBody(true)->getContents());
            return $response;
        } 
        else
        {
            $response = json_decode($e->getResponse()->getBody(true)->getContents());
            return $response;
        }
    }
}
