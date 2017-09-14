<?php

namespace App\Providers;


use App\Config\SiteConstants;


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
        $mail = '';
        $showPrompt = isset($_SESSION[SiteConstants::ShowLoginPrompt])?$_SESSION[SiteConstants::ShowLoginPrompt]:false;

        if (!$showPrompt) {
            if($mail)
                $url = $this->AddNewParameter($url,'login_hint',$mail);
        }else{
            $url = $this->AddNewParameter($url,'prompt','login');
        }
        return $url;
    }

    private function AddNewParameter($url,$parameter,$value)
    {
        if (strpos($url, '?') > 0) {
            return $url . '&' . $parameter .'=' . $value;
        } else {
            return $url = $url . '?' . $parameter .'=' . $value;
        }
    }

}