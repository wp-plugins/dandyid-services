<?php

class dandyid
    {
    // Declare class data vars
    private $api_key         = "";
    private $api_token       = "";
    private $site            = "";
    private $postFields      = "";
    private $urlAppend       = "";
    private $user_identifier = "";
    private $email_address   = "";
    private $password        = "";


    public function __construct ()
        {
        }


    public function setAPIFields ($api_key, $api_token, $site)
        {
        $this->api_key   = $api_key;
        $this->api_token = $api_token;
        $this->site      = $site;
        }


    public function setUserFields ($user_identifier, $email_address, $password)
        {
        $this->user_identifier = $user_identifier;
        $this->email_address   = $email_address;
        $this->password        = $password;
        }


    public function return_services ()
        {
        // The URL being requested
        // http://www.dandyId.org/api/return_services/{api_key}/{user_identifier}

        $this->urlAppend = "return_services"                   . "/" .
                            urlencode ($this->api_key)         . "/" .
                            urlencode ($this->user_identifier);

        $this->postFields = "api_token=" . urlencode ($this->api_token);

        return ($this->process ());
        }


    public function service_details ($svcId)
        {
        // The URL being requested
        // http://www.dandyId.org/api/service_details/{api_key}/{svcId}

        $this->urlAppend = "service_details"           . "/" .
                            urlencode ($this->api_key) . "/" .
                            urlencode ($svcId);

        $this->postFields = "api_token=" . urlencode ($this->api_token);

        return ($this->process ());
        }


    public function sync_user ()
        {
        // The URL being requested
        // http://www.dandyId.org/api/sync_user/{api_key}/{user_identifier}

        $this->urlAppend   = "sync_user"                         . "/" .
                              urlencode ($this->api_key)         . "/" .
                              urlencode ($this->user_identifier);

        $this->postFields  = "email_address=" . urlencode ($this->email_address) .
                             "&password="     . urlencode ($this->password)      .
                             "&api_token="    . urlencode ($this->api_token);

        $this->process ();
        }


    public function process ()
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
