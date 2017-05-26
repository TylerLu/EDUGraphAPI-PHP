<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Services;

use App\Config\SiteConstants;

class CookieService
{
    private $usernameCookie = SiteConstants::UsernameCookie;
    private $emailCookie = SiteConstants::EmailCookie;

    public function setCookies($username, $email)
    {
        $time = time() + 8640000;
        setcookie($this->usernameCookie, $username, $time, '/');
        setcookie($this->emailCookie, $email, $time, '/');
    }

    public function getCookiesOfUsername()
    {
        if (!isset($_COOKIE[$this->usernameCookie])) {
            return null;
        }
        return $_COOKIE[$this->usernameCookie];
    }

    public function getCookiesOfEmail()
    {
        if (!isset($_COOKIE[$this->emailCookie])) {
            return null;
        }
        return $_COOKIE[$this->emailCookie];
    }

    public function clearCookies()
    {
        setcookie($this->usernameCookie, "", time() - 3600);
        setcookie($this->emailCookie, "", time() - 3600);
    }
}