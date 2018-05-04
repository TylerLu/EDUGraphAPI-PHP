<?php

//require('JWT.php');
class MSGraphHelper
{
    public  $certPath = 'app_only_cert.pfx';
    public  $certPassword = 'J48W23RQeZv85vj';

    public function __construct()
    {
//        $this->certPath = getenv("Cert_Path");;
//        $this->certPassword = getenv("Cert_password");;
    }



    public  function  getProvider()
    {
        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => '4e3fa16f-9909-4bf6-9a66-5560e97e7082',    // The client ID assigned to you by the provider
            'clientSecret'            => 'IO0DqycDalD832ANxFFnVn9zwIjr/I3XXY1rjhinq/s=',    // The client password assigned to you by the provider
            'redirectUri'             => 'http://test/',
            'urlAuthorize'            => 'https://graph.microsoft.com/oauth2/authorize',
            'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/token',
            'urlResourceOwnerDetails' => ''
        ]);
        return $provider;

    }

    public   function getAccessToken($tenantId,$clientId)
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