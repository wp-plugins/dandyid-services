<?php

class dandyid
    {
    // Declare class data vars
    var $api_key         = "";
    var $api_token       = "";
    var $site            = "";
    var $postFields      = "";
    var $urlAppend       = "";
    var $user_identifier = "";
    var $email_address   = "";


    function __construct ()
        {
        }


    function setAPIFields ($api_key, $api_token, $site)
        {
        $this->api_key   = $api_key;
        $this->api_token = $api_token;
        $this->site      = $site;
        }


    function setUserFields ($user_identifier, $email_address)
        {
        $this->user_identifier = md5 ($user_identifier);
        $this->email_address   = $email_address;
        }


    function return_services ()
        {
        // The URL being requested
        // http://www.dandyId.org/api/return_services/{api_key}/{user_identifier}

        $this->urlAppend = "return_services"                   . "/" .
                            urlencode ($this->api_key)         . "/" .
                            urlencode ($this->user_identifier) . "/public";

        $this->postFields = "api_token=" . urlencode ($this->api_token);

        return ($this->process ());
        }


    function service_details ($svcId)
        {
        // The URL being requested
        // http://www.dandyId.org/api/service_details/{api_key}/{svcId}

        $this->urlAppend = "service_details"           . "/" .
                            urlencode ($this->api_key) . "/" .
                            urlencode ($svcId);

        $this->postFields = "api_token=" . urlencode ($this->api_token);

        return ($this->process ());
        }


    function process ()
        {
        // Create a new cURL resource
        $ch = curl_init ();

        // Construct URL
        $curlUrl = $this->site . $this->urlAppend;

        // Set cURL options
        curl_setopt ($ch, CURLOPT_POST,           1);
        curl_setopt ($ch, CURLOPT_POSTFIELDS,     $this->postFields);
        curl_setopt ($ch, CURLOPT_URL,            $curlUrl);
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);

        // Exec the URL
        $response = curl_exec ($ch);

        // Close cURL resource, and free up system resources
        curl_close ($ch);

        return ($response);
        }
    }
