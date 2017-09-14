<?php
namespace Microsoft\Graph\Connect;

class Constants
{

    const CLIENT_ID               = 'CLIENT_ID';
    const CLIENT_SECRET           = 'CLIENT_SECRET';
    const SOURCECODERESPOSITORYURL= 'SOURCECODERESPOSITORYURL';

    const AUTHORITY_URL        = 'https://login.microsoftonline.com/common';
    const AUTHORIZE_ENDPOINT   = '/oauth2/authorize';
    const TOKEN_ENDPOINT       = '/oauth2/token';
    const MSGraph          = 'https://graph.microsoft.com';
    const AADGraph         = 'https://graph.windows.net';
    const MSGraph_VERSION  ='beta';

    const O365GroupConversationsUrlFormat = "https://outlook.office.com/owa/?path=/group/%s/mail&exsvurl=1&ispopout=0";

}