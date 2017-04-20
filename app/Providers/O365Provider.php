<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Providers;


use App\Services\CookieService;

class O365Provider extends \SocialiteProviders\Azure\Provider
{

    protected $version = '1.6';

    /**
     * Overwrite the base method to enable login_hint.
     * {@inheritdoc}
     * If there's cookie for email, add a login_hint on O365 login url.
     */
    protected function getAuthUrl($state)
    {
        $url = parent::getAuthUrl($state);
        //login_hint
        $mail = (new CookieService)->GetCookiesOfEmail();
        if ($mail) {
            if (strpos($url, '?') > 0) {
                $url = $url . '&' . 'login_hint=' . $mail;
            } else {
                $url = $url . '?' . 'login_hint=' . $mail;
            }
        }
        return $url;
    }

}
