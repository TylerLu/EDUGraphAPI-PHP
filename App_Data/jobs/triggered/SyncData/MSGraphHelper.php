<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

class MSGraphHelper
{
    public  $certPath = null;
    public  $certPassword = null;

    public function __construct()
    {
        $this->certPath = getenv("Cert_Path");;
        $this->certPassword = getenv("Cert_password");
    }

    public function queryUsers($url,$tenantId,$clientId)
    {
        $accessToken  =$this->getAccessToken($tenantId,$clientId);
        $nextLink = $url;
        $deltaLink='';
        $users = array();

        $request_headers   = array();
        $request_headers[] ='Authorization: Bearer '.$accessToken;
        $curl = curl_init();
        while(true)
        {
            curl_setopt_array($curl, array(
                CURLOPT_URL => $nextLink,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "",
                CURLOPT_HTTPHEADER => $request_headers,
            ));
            $response = curl_exec($curl);
            $json_a = json_decode($response, true);

            if(isset($json_a["value"])) {
                foreach ($json_a["value"] as $item) {
                    $user = new SyncData\GraphUser();
                    if(isset($item["department"]))
                    {
                        $user->department = $item["department"];
                    }
                    else{
                        $user->department="";
                    }
                    if(isset($item["jobTitle"]))
                    {
                        $user->jobTitle = $item["jobTitle"];
                    }
                    else{
                        $user->jobTitle="";
                    }
                    if(isset($item["mobilePhone"]))
                    {
                        $user->mobilePhone = $item["mobilePhone"];
                    }
                    else{
                         $user->mobilePhone="";
                    }
                    if(isset($item["@removed"]))
                    {
                        $user->isRemoved = true;
                    }
                    else{
                        $user->isRemoved=false;
                    }
                    $user->id = $item["id"];
                    array_push($users,$user);
                }
            }
            if(isset($json_a["@odata.nextLink"]))
            {
                $nextLink  = $json_a["@odata.nextLink"];
            }
            else
            {
                if(isset($json_a["@odata.deltaLink"])){
                    $deltaLink = $json_a["@odata.deltaLink"];    
                } 
                break;
            }
        }
        error_log("Get  ".count($users)." users.");
        return array($users,$deltaLink);
    }

    public function getAccessToken($tenantId,$clientId)
    {
        $jwt = $this->getJWT($tenantId,$clientId);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://login.microsoftonline.com/".$tenantId."/oauth2/token?api-version=1.0",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "grant_type=client_credentials&client_id=".$clientId."&resource=https://graph.microsoft.com&client_assertion_type=urn:ietf:params:oauth:client-assertion-type:jwt-bearer&client_assertion=".$jwt,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded"
            ),
        ));
        $response = curl_exec($curl);
        $json_a = json_decode($response, true);

        return $json_a["access_token"];
    }

    function getJWT($tenantId,$clientId)
    {
        $payload =
            ['aud'=> 'https://login.microsoftonline.com/'.$tenantId.'/oauth2/token',
                'iss'=> $clientId,
                'sub'=> $clientId,
                'nbf'=> $_SERVER['REQUEST_TIME'] + 10200,
                'exp'=> $_SERVER['REQUEST_TIME'] + 20200,
                'jti'=> 'fe697c1f-5695-463c-a2b8-756065540ce3'
            ];
        $cert = $this-> getCert();
        $key = $cert['pkey'];
        $jwt = \Firebase\JWT\JWT::encode($payload, $key,'RS256',null,['x5t'=> 'u1eozIBvJUJ_0BtXR4wMWdf2JIY=']);
        return $jwt;
    }

    function getCert()
    {
        $certs = array();
        $pkcs12 = file_get_contents( $this->certPath);
        openssl_pkcs12_read( $pkcs12, $certs, $this->certPassword );
        return  $certs ;
    }

}
