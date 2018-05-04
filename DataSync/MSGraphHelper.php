<?php
class MSGraphHelper
{


    public function __construct()
    {

    }
    public static  function getCert($certPath,$certPassword)
    {
        $certPath = 'EduGraphAPI App Only Cert.pfx';
        $certPassword = 'J48W23RQeZv85vj';
        $certs = array();
        $pkcs12 = file_get_contents($certPath );
        openssl_pkcs12_read( $pkcs12, $certs, $certPassword );
        return  $certs ;
    }

    public  static  function  getProvider()
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

    public static  function getAccessToken()
    {
        return 'eyJ0eXAiOiJKV1QiLCJub25jZSI6IkFRQUJBQUFBQUFEWDhHQ2k2SnM2U0s4MlRzRDJQYjdyeWlvRDBnVjFERHVIZFRpeTl3T1A0UUNEcFVmYXREOW5JMUdCR3kzQ2sxUWE5ZExaWEYxbUdNME9PMk4yRmhHNjdyZV9ral92dkpyWl9KS1pSWWtPSlNBQSIsImFsZyI6IlJTMjU2IiwieDV0IjoiaUJqTDFSY3F6aGl5NGZweEl4ZFpxb2hNMllrIiwia2lkIjoiaUJqTDFSY3F6aGl5NGZweEl4ZFpxb2hNMllrIn0.eyJhdWQiOiJodHRwczovL2dyYXBoLm1pY3Jvc29mdC5jb20iLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC82NDQ0NmI1Yy02ZDg1LTRkMTYtOWZmMi05NGVkZGMwYzI0MzkvIiwiaWF0IjoxNTI1MzM4MjQxLCJuYmYiOjE1MjUzMzgyNDEsImV4cCI6MTUyNTM0MjE0MSwiYWlvIjoiWTJkZ1lKanlJVFhDekUxbkxVLzNKdVBwQWlzZUF3QT0iLCJhcHBfZGlzcGxheW5hbWUiOiJFRFVHcmFwaEFQSSBEZXYgLSBQeXRob24iLCJhcHBpZCI6ImRmYzgxYjk1LTFhOWMtNDUyMi05ZjMzLTI1OWRlOWFjZjY4YiIsImFwcGlkYWNyIjoiMiIsImVfZXhwIjoyNjI4MDAsImlkcCI6Imh0dHBzOi8vc3RzLndpbmRvd3MubmV0LzY0NDQ2YjVjLTZkODUtNGQxNi05ZmYyLTk0ZWRkYzBjMjQzOS8iLCJvaWQiOiI0YzcyNj
A2Mi05ZDJmLTRjMWYtODdiOS1hYWUxNzBiNTk0NjkiLCJyb2xlcyI6WyJVc2VyLlJlYWQuQWxsIl0sInN1YiI6IjRjNzI2MDYyLTlkMmYtNGMxZi04N2I5LWFhZTE3MGI1OTQ2OSIsInR
pZCI6IjY0NDQ2YjVjLTZkODUtNGQxNi05ZmYyLTk0ZWRkYzBjMjQzOSIsInV0aSI6IjVHN05qUmN3SGtTcVJOYWdiYWdFQUEiLCJ2ZXIiOiIxLjAifQ.mKqAMI6QQn1nZRMZvgVlqmyup-FQCMZG_sWeXcjHfy54JTFWX0t_z9KOtSAMoZ96v7hzwi5KS-GiENcWz0HzHb5tPGgi0cfrugJje4rS1p9ldO0BpLoyR-VIuYV6TIr6jdXsnr8uBOR2tdhKOBh-GklYr4hbw-Ut-s_YqXGAX-0jXB02n3ySNoq3iBbbPJTpvQy0xy9tVQB2-eUtLBSQzAeKlFxm-x3RWtsceMJ9wNr78bbrUXxpyzZIxNkhFpxZ0ytrW3YBkkmypxqnvpqi9qD7pijfTnrBf1TmBGFGhz9uzk0DUSS52b7HpkzatSgPoPquDVs_ZRFI3RoaplaDrg';
    }

    public function getJWT($key)
    {

    }
}