<?php

namespace App\Providers;

use SocialiteProviders\Manager\SocialiteWasCalled;

class O365ExtendSocialite
{

    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite(
            'O365', __NAMESPACE__.'\O365Provider'
        );
    }
}