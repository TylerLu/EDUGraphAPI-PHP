
<?php
require_once ('vendor/autoload.php');
require('MSGraphHelper.php');
require('DbHelper.php');

$helper = new MSGraphHelper();
$tenantId = '64446b5c-6d85-4d16-9ff2-94eddc0c2439';
$clientId = 'dfc81b95-1a9c-4522-9f33-259de9acf68b';
$jwt = $helper->getAccessToken($tenantId,$clientId);
//echo( $jwt);

//
//use \Firebase\JWT\JWT;
//$tenantId = '64446b5c-6d85-4d16-9ff2-94eddc0c2439';
//$clientId='dfc81b95-1a9c-4522-9f33-259de9acf68b';
//$header = ['typ'=> 'JWT', 'alg'=> 'RS256', 'x5t'=> 'u1eozIBvJUJ_0BtXR4wMWdf2JIY='];
//$payload =
//    ['aud'=> 'https://login.microsoftonline.com/'.$tenantId.'/oauth2/token',
//        'iss'=> $clientId,
//        'sub'=> $clientId,
//        'nbf'=> $_SERVER['REQUEST_TIME'] + 10200,
//        'exp'=> $_SERVER['REQUEST_TIME'] + 20200,
//        'jti'=> 'fe697c1f-5695-463c-a2b8-756065540ce3'
//    ];
//
//
//$cert = $helper->getCert();
//$key=$cert['pkey'];
//
//$jwt = JWT::encode($payload, $key,'RS256',null,['x5t'=> 'u1eozIBvJUJ_0BtXR4wMWdf2JIY=']);
//echo $jwt;

//$clientId = 'dfc81b95-1a9c-4522-9f33-259de9acf68b';
//$token = MSGraphHelper::getAccessToken();
//$dbHelper = new DBHelper();
//$organizations = $dbHelper->getOrganizations();
//foreach ($organizations as $r)
//{
//
//   var_dump( $dbHelper->getOrCreateDataSyncRecord($r["tenantId"]));
//}


//Do Not remove
//require('DbHelper.php');
//$sql = 'select * from users';
//$helper = new DBHelper();
//$result = $helper->execute($sql);
//foreach ($result as $r)
//{
//    echo $r["firstName"];
//}


//$certPath = 'EduGraphAPI App Only Cert.pfx';
//$certPassword = getenv("CertPassword");;
//
//$cert =  Common::getCert($certPath,$certPassword);
//
////var_dump($cert);
////
////var_dump($cert['cert']);
//
//$porovider = Common::getProvider();

//try {
//
//    // Try to get an access token using the client credentials grant.
//    $accessToken = $porovider->getAccessToken('password', [
//        'username' => 'admin@canvizedu.onmicrosoft.com',
//        'password' => 'C@nviz@EDU17'
//    ]);
//
//    var_dump($accessToken);
//} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
//
//    // Failed to get the access token
//    exit($e->getMessage());
//
//}


//try {
//
//    // Try to get an access token using the client credentials grant.
//    $accessToken = $provider->getAccessToken($cert['cert']);
//
//    var_dump($accessToken);
//
//} catch (IdentityProviderException $e) {
//
//    // Failed to get the access token
//    exit($e->getMessage());
//
//}

