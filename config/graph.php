<?php
/**
 *  Copyright (c) Microsoft. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 *
 *  PHP version 5
 *
 *  @category Code_Sample
 *  @package  php-connect-sample
 *  @author   Microsoft
 *  @license  MIT License
 *  @link     http://github.com/microsoftgraph/php-connect-sample
 */

namespace Microsoft\Graph\Connect;

/**
 *  Stores constant and configuration values used through the app
 *
 *  @class    Constants
 *  @category Code_Sample
 *  @package  php-connect-sample
 *  @author   Microsoft
 *  @license  MIT License
 *  @link     http://github.com/microsoftgraph/php-connect-sample
 */
class Constants
{

    const CLIENT_ID               = 'CLIENT_ID';
    const CLIENT_SECRET           = 'CLIENT_SECRET';
    const BINGMAPKEY              = 'BINGMAPKEY';
    const SOURCECODERESPOSITORYURL= 'SOURCECODERESPOSITORYURL';

    const AUTHORITY_URL        = 'https://login.microsoftonline.com/common';
    const AUTHORIZE_ENDPOINT   = '/oauth2/authorize';
    const TOKEN_ENDPOINT       = '/oauth2/token';
    const RESOURCE_ID          = 'https://graph.microsoft.com';
    const AADGraph             = 'https://graph.windows.net';

    const O365GroupConversationsUrlFormat = "https://outlook.office.com/owa/?path=/group/%s/mail&exsvurl=1&ispopout=0";



}
