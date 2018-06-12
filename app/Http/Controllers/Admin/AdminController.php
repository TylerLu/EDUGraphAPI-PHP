<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Http\Controllers\Admin;

use App\Config\SiteConstants;
use App\Config\UserType;
use App\Http\Controllers\Controller;
use App\Model\Organizations;
use App\Services\AADGraphService;
use App\Services\AdminService;
use App\Services\AuthenticationHelper;
use App\Services\OrganizationsService;
use App\Services\TokenCacheService;
use App\Services\UserService;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Lcobucci\JWT\Parser;
use Microsoft\Graph\Connect\Constants;

class AdminController extends Controller
{
    private $aadGraphService;
    private $tokenCacheService;
    private $adminService;
    private $organizationsService;
    private $userServices;

    public function __construct()
    {
        $this->aadGraphService = new AADGraphService();
        $this->tokenCacheService = new TokenCacheService();
        $this->adminService = new AdminService();
        $this->organizationsService = new OrganizationsService();
        $this->userServices = new UserService();
    }

    public function index()
    {
        $IsAdminConsented = false;
        $user = Auth::user();
        $o365UserId = $user->o365UserId;
        $token = $this->tokenCacheService->getMSGraphToken($o365UserId);
        $tenantId = $this->aadGraphService->getTenantIdByUserId($o365UserId, $token);
        if ($tenantId) {
            $org = $this->organizationsService->getOrganization($tenantId);
            if ($org && $org->isAdminConsented) {
                $IsAdminConsented = true;
            } else {
                $_SESSION[SiteConstants::Session_RedirectURL] = '/admin';
            }
        }
        $msg = '';
        $successMsg = '';
        $consented = Input::get('consented');
        if ($consented != null) {
            if ($consented === 'true')
                $successMsg = SiteConstants::AdminConsentSucceedMessage;
            else
                $successMsg = SiteConstants::AdminUnconsentMessage;
        }
        if (isset($_GET['msg'])) {
            $msg = $_GET['msg'];
        }
        if (isset($_GET['successMsg'])) {
            $successMsg = $_GET['successMsg'];
        }
        $arrData = array(
            'IsAdminConsented' => $IsAdminConsented,
            'msg' => $msg,
            'successMsg' => $successMsg
        );
        return view('admin.index', $arrData);
    }

    /**
     * Consent without login.
     */
    public function consent()
    {
        $_SESSION[SiteConstants::Session_RedirectURL] = '/admin/consent';
        $consented = false;
        $msg = '';
        if (Input::get('consented')) {
            $consented = true;
            $msg = SiteConstants::AdminConsentSucceedMessage;
        }
        $arrData = array(
            'consented' => $consented,
            'msg' => $msg
        );
        return view('admin.consent', $arrData);

    }

    /**
     * Redirect admin to consent page.
     */
    public function adminConsent()
    {
        $user = Auth::user();
        $redirectUrl = 'http' . (empty($_SERVER['HTTPS']) ? '' : 's') . '://' . $_SERVER['HTTP_HOST'] . '/admin/processcode';
        $state = uniqid();
        $_SESSION[SiteConstants::Session_State] = $state;
        $url = $this->adminService->getConsentUrl($state, $redirectUrl);
        $url = $this->AddNewParameter($url,'login_hint',$user->email);
        header('Location: ' . $url);
        exit();
    }

    /**
     * Process consent result after consent.
     */
    public function processCode()
    {
        $code = Input::get('code');
        $state = Input::get('state');
        if (!isset($_SESSION[SiteConstants::Session_State]) || $_SESSION[SiteConstants::Session_State] != $state || !$code) {
            return back()->with('msg', 'Invalid operation. Please try again.');
        }
        if ($code) {
            $redirectUrl = 'http' . (empty($_SERVER['HTTPS']) ? '' : 's') . '://' . $_SERVER['HTTP_HOST'] . '/admin/processcode';
            $msGraphToken = $this->adminService->getMSGraphToken($redirectUrl, $code);
            $idToken = $msGraphToken->getValues()['id_token'];
            $parsedToken = (new Parser())->parse((string)$idToken);
            $tenantId = $parsedToken->getClaim('tid');
            $this->organizationsService->setTenantConsentResult($tenantId, true);
        }

        $redirectUrl = '/admin';
        if (isset($_SESSION[SiteConstants::Session_RedirectURL])) {
            $redirectUrl = $_SESSION[SiteConstants::Session_RedirectURL];
            unset($_SESSION[SiteConstants::Session_RedirectURL]);
        }
        header('Location: ' . $redirectUrl . '?consented=true');
        exit();
    }

    public function adminUnconsent()
    {
        $user = Auth::user();
        $o365UserId = $user->o365UserId;
        $tenantId = $this->aadGraphService->getTenantIdByUserId($o365UserId, $this->tokenCacheService->getMSGraphToken($o365UserId));
        $this->adminService->unconsent($tenantId, $this->tokenCacheService->getAADToken($o365UserId));
        $org = $this->organizationsService->getOrganization($tenantId);
        $this->userServices->unlinkAllUsers($org->id);

        Session::flush();
        $_SESSION=array();
        session_destroy();
        Auth::logout();

        header('Location: ' . '/admin/consent');
        exit();

    }

    public function manageLinkedAccounts()
    {
        $user = Auth::user();
        $o365UserId = $user->o365UserId;
        $token = $this->tokenCacheService->getMSGraphToken($o365UserId);
        $tenantId = $this->aadGraphService->getTenantIdByUserId($o365UserId, $token);
        $org = $this->organizationsService->getOrganization($tenantId);
        $users = [];
        if ($org) {
            $users = $this->userServices->getUsers($org->id);
        }
        return view('admin.manageaccounts', compact('users'));
    }

    public function unlinkAccount($userId)
    {
        $user = $this->userServices->getUserById($userId);
        if (!$user)
            return redirect('/admin/linkedaccounts');
        return view('admin.unlinkaccount', compact('user'));
    }

    public function doUnlink($userId)
    {
        $this->userServices->unlinkUser($userId);
        return redirect('/admin/linkedaccounts');
    }

    public function enableUserAccess()
    {
        set_time_limit(1200);
        $this->adminService->enableUsersAccess();
    }

    public function clearAdalCache()
    {
        $this->tokenCacheService->clearUserTokenCache();
        $message = 'Login cache cleared successfully!';
        header('Location: ' . '/admin?successMsg=' . $message);
        exit();
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
