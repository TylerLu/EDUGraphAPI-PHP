<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Http\Controllers\Admin;

use App\Config\SiteConstants;
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
        $token = $this->tokenCacheService->GetMSGraphToken($o365UserId);
        $tenantId = $this->aadGraphService->GetTenantIdByUserId($o365UserId, $token);
        if ($tenantId) {
            $org = $this->organizationsService->GetOrganization($tenantId);
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
    public function AdminConsent()
    {
        $redirectUrl = 'http' . (empty($_SERVER['HTTPS']) ? '' : 's') . '://' . $_SERVER['HTTP_HOST'] . '/admin/processcode';
        $state = uniqid();
        $_SESSION[SiteConstants::Session_State] = $state;
        $url = $this->adminService->getConsentUrl($state, $redirectUrl);
        header('Location: ' . $url);
        exit();
    }

    /**
     * Process consent result after consent.
     */
    public function ProcessCode()
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
            $this->organizationsService->SetTenantConsentResult($tenantId, true);
        }

        $redirectUrl = '/admin';
        if (isset($_SESSION[SiteConstants::Session_RedirectURL])) {
            $redirectUrl = $_SESSION[SiteConstants::Session_RedirectURL];
            unset($_SESSION[SiteConstants::Session_RedirectURL]);
        }
        header('Location: ' . $redirectUrl . '?consented=true');
        exit();
    }

    public function AdminUnconsent()
    {
        $user = Auth::user();
        $o365UserId = $user->o365UserId;
        $tenantId = $this->aadGraphService->GetTenantIdByUserId($o365UserId, $this->tokenCacheService->GetMSGraphToken($o365UserId));
        $this->adminService->unconsent($tenantId, $this->tokenCacheService->GetAADToken($o365UserId));
        header('Location: ' . '/admin?consented=false');
        exit();

    }

    public function MangeLinkedAccounts()
    {
        $user = Auth::user();
        $o365UserId = $user->o365UserId;
        $token = $this->tokenCacheService->GetMSGraphToken($o365UserId);
        $tenantId = $this->aadGraphService->GetTenantIdByUserId($o365UserId, $token);
        $org = $this->organizationsService->GetOrganization($tenantId);
        $users = [];
        if ($org) {
            $users = $this->userServices->getUsers($org->id);
        }
        return view('admin.manageaccounts', compact('users'));
    }

    public function UnlinkAccount($userId)
    {
        $user = $this->userServices->getUserById($userId);
        if (!$user)
            return redirect('/admin/linkedaccounts');
        return view('admin.unlinkaccount', compact('user'));
    }

    public function DoUnlink($userId)
    {
        $this->userServices->unlinkUser($userId);
        return redirect('/admin/linkedaccounts');
    }

    public function EnableUserAccess()
    {
        set_time_limit(1200);
        $this->adminService->enableUsersAccess();
    }
}
