<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Http\Controllers\Admin;

use App\Config\SiteConstants;
use App\Http\Controllers\Controller;
use App\Model\Organizations;
use App\Services\AADGraphClient;
use App\Services\AuthenticationHelper;
use App\Services\OrganizationsService;
use App\Services\TokenCacheService;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Lcobucci\JWT\Parser;
use Microsoft\Graph\Connect\Constants;

class AdminController extends Controller
{

    public function index()
    {

        $IsAdminConsented = false;
        $user = Auth::user();
        $o365UserId = $user->o365UserId;
        $tenantId = (new AADGraphClient)->GetTenantIdByUserId($o365UserId);
        if ($tenantId) {
            $org = Organizations::where('tenantId', $tenantId)->first();
            if ($org && $org->isAdminConsented) {
                $IsAdminConsented = true;
            } else {
                $_SESSION[SiteConstants::Session_RedirectURL] = '/admin';
            }
        }
        $msg = '';
        $successMsg='';
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
            'successMsg'=>$successMsg
        );
        return view('admin.index', $arrData);
    }

    /**
     * Admin can do consent directly without login to the APP.
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
     * Redirect admin to O365 login and consent page.
     */
    public function AdminConsent()
    {
        $redirectUrl = $_SERVER['HTTP_HOST'] . '/admin/processcode';
        $state = uniqid();
        $_SESSION[SiteConstants::Session_State] = $state;

        $provider = (new AuthenticationHelper())->GetProvider($redirectUrl);

        $url = $provider->getAuthorizationUrl([
            'response_type' => 'code',
            'resource' => Constants::AADGraph,
            'state' => $state,
            'prompt' => SiteConstants::AdminConsent
        ]);

        header('Location: ' . $url);
        exit();
    }

    /**
     * Process consent result after consent and return from O365.
     */
    public function ProcessCode()
    {
        $code = Input::get('code');
        $state = Input::get('state');
        if (!isset($_SESSION[SiteConstants::Session_State]) || $_SESSION[SiteConstants::Session_State] != $state || !$code) {
            return back()->with('msg', 'Invalid operation. Please try again.');
        }
        if ($code) {
            $redirectUrl = $_SERVER['HTTP_HOST'] . '/admin/processcode';
            $provider = (new AuthenticationHelper())->GetProvider($redirectUrl);
            $microsoftToken = $provider->getAccessToken('authorization_code', [
                'code' => $code,
                'resource' => Constants::RESOURCE_ID
            ]);
            $idToken = $microsoftToken->getValues()['id_token'];
            $parsedToken = (new Parser())->parse((string)$idToken);
            $tenantId = $parsedToken->getClaim('tid');
            (new OrganizationsService)->SetTenantConsentResult($tenantId, true);
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
        $token = (new TokenCacheService)->GetAADToken($o365UserId);
        $tenantId = (new AADGraphClient)->GetTenantIdByUserId($o365UserId);
        $url = Constants::AADGraph . '/' . $tenantId . '/servicePrincipals/?api-version=1.6&$filter=appId%20eq%20\'' . env(Constants::CLIENT_ID) . '\'';
        $client = new \GuzzleHttp\Client();
        $result = $client->request('GET', $url, [
            'headers' => [
                'Content-Type' => 'application/json;odata.metadata=minimal;odata.streaming=true',
                'Authorization' => 'Bearer ' . $token
            ]
        ]);
        $app = json_decode($result->getBody())->value;
        $appId = $app[0]->objectId;
        $url = Constants::AADGraph . '/' . $tenantId . '/servicePrincipals/' . $appId . '?api-version=1.6';
        $result = $client->request('DELETE', $url, [
            'headers' => [
                'Content-Type' => 'application/json;odata.metadata=minimal;odata.streaming=true',
                'Authorization' => 'Bearer ' . $token
            ]
        ]);
        (new OrganizationsService)->SetTenantConsentResult($tenantId, false);

        header('Location: ' . '/admin?consented=false');
        exit();

    }

    public function MangeLinkedAccounts()
    {
        $user = Auth::user();
        $o365UserId = $user->o365UserId;
        $tenantId = (new AADGraphClient)->GetTenantIdByUserId($o365UserId);
        $org = (new OrganizationsService)->GetOrganization($tenantId);
        $users = [];
        if ($org) {
            $users = User::where('OrganizationId', $org->id)
                ->where('o365UserId', '!=', null)
                ->where('o365UserId', '!=', '')->get();
        }
        return view('admin.manageaccounts', compact('users'));
    }

    public function UnlinkAccount($userId)
    {
        $user = User::where('id', $userId)->first();
        if (!$user)
            return redirect('/admin/linkedaccounts');
        return view('admin.unlinkaccount', compact('user'));
    }

    public function DoUnlink($userId)
    {
        $user = User::where('id', $userId)->first();
        if (!$user)
            return redirect('/admin/linkedaccounts');
        $user->o365Email = null;
        $user->o365UserId = null;
        $user->save();
        return redirect('/admin/linkedaccounts');
    }

    public function EnableUserAccess()
    {
        set_time_limit(1200);
        $user = Auth::user();
        $o365UserId = $user->o365UserId;
        $token = (new TokenCacheService)->GetAADToken($o365UserId);
        $tenantId = (new AADGraphClient)->GetTenantIdByUserId($o365UserId);
        $url = Constants::AADGraph . '/' . $tenantId . '/servicePrincipals/?api-version=1.6&$filter=appId%20eq%20\'' . env(Constants::CLIENT_ID) . '\'';
        $client = new \GuzzleHttp\Client();
        $app = null;
        $authHeader = [
            'headers' => [
                'Content-Type' => 'application/json;odata.metadata=minimal;odata.streaming=true',
                'Authorization' => 'Bearer ' . $token
            ]
        ];
        try {
            $result = $client->request('GET', $url, $authHeader);
            $app = json_decode($result->getBody())->value;
            $servicePrincipalId = $app[0]->objectId;
            $servicePrincipalName = $app[0]->appDisplayName;
        } catch (\Exception $e) {
            return back()->with('msg', SiteConstants::NoPrincipalError);
        }

        try {
            $this->AddAppRoleAssignmentForUsers($authHeader, null, $tenantId, $servicePrincipalId, $servicePrincipalName);

        } catch (\Exception $e) {
            return back()->with('msg', SiteConstants::EnableUserAccessFailed);
        }
        $count = '0';
        if (isset($_SESSION[SiteConstants::Session_EnabledUserCount]))
            $count = (int)$_SESSION[SiteConstants::Session_EnabledUserCount];
        $message = 'There\'re no users in your tanent.';
        if ($count > 0)
            $message = 'User access was successfully enabled for ' . $count . ' users.';
        $_SESSION[SiteConstants::Session_EnabledUserCount] = null;
        header('Location: ' . '/admin?successMsg=' . $message);
        exit();
    }

    private function AddAppRoleAssignmentForUsers($authHeader, $nextLink, $tenantId, $servicePrincipalId, $servicePrincipalName)
    {

        $url = Constants::AADGraph . '/' . $tenantId . '/users?api-version=1.6&$expand=appRoleAssignments';
        if ($nextLink) {
            $url = $url . "&" . $this->GetSkipToken($nextLink);
        }
        $client = new \GuzzleHttp\Client();
        $result = $client->request('GET', $url, $authHeader);
        $response = json_decode($result->getBody());
        $users = $response->value;

        $this->AddAppRoleAssignment($authHeader, $users, $servicePrincipalId, $servicePrincipalName, $tenantId);
        if (!isset($_SESSION[SiteConstants::Session_EnabledUserCount]))
            $_SESSION[SiteConstants::Session_EnabledUserCount] = count($users);
        else {
            $count = (int)$_SESSION[SiteConstants::Session_EnabledUserCount];
            $count += count($users);
            $_SESSION[SiteConstants::Session_EnabledUserCount] = $count;
        }
        if (isset(get_object_vars($response)['odata.nextLink']))
            $nextLink = get_object_vars($response)['odata.nextLink'];
        else {
            $nextLink = null;
        }
        if ($nextLink) {
            $this->AddAppRoleAssignmentForUsers($authHeader, $nextLink, $tenantId, $servicePrincipalId, $servicePrincipalName);
        }

    }

    private function AddAppRoleAssignment($authHeader, $users, $servicePrincipalId, $servicePrincipalName, $tenantId)
    {
        $count = count($users);
        $client = new \GuzzleHttp\Client();

        for ($i = 0; $i < $count; $i++) {
            $user = $users[$i];
//            if ($user->objectId != 'adbd9250-c0a9-46a9-addf-743fc6b31ed6')
//                continue;
            $roleAssignment = $user->appRoleAssignments;
            $roles = count($roleAssignment);
            $servicePrincipalExists = false;
            for ($j = 0; $j < $roles; $j++) {
                if ($roleAssignment[$j]->resourceId == $servicePrincipalId) {
                    return;
                }
            }
            if (!$servicePrincipalExists) {

                if (!isset($roleAssignment['odata.nextLink'])) {
                    $this->DoAddRole($authHeader, $user, $servicePrincipalId, $servicePrincipalName, $tenantId);
                } else {
                    $url = Constants::AADGraph . '/' . $tenantId . '/users/' . $user->objectId . '/appRoleAssignments?api-version=1.6&$filter=resourceId%20eq%20guid\'' . $servicePrincipalId . '\'';
                    $result = $client->request('GET', $url, $authHeader);
                    $response = json_decode($result->getBody());
                    if (!$response->value) {
                        $this->DoAddRole($authHeader, $user, $servicePrincipalId, $servicePrincipalName, $tenantId);
                    }
                }
            }
        }
    }

    private function DoAddRole($authHeader, $user, $servicePrincipalId, $servicePrincipalName, $tenantId)
    {

        $client = new \GuzzleHttp\Client();
        $body = [
            'odata.type' => 'Microsoft.DirectoryServices.AppRoleAssignment',
            'principalDisplayName' => $user->displayName,
            'principalId' => $user->objectId,
            'principalType' => 'User',
            'resourceId' => $servicePrincipalId,
            'resourceDisplayName' => $servicePrincipalName
        ];

        $authHeader['body'] = json_encode($body);

        $url = Constants::AADGraph . '/' . $tenantId . '/users/' . $user->objectId . '/appRoleAssignments?api-version=1.6';
        $res = $client->request('POST', $url, $authHeader);

    }


    private function GetSkipToken($nextLink)
    {
        $pattern = '/\$skiptoken=[^&]+/';
        preg_match($pattern, $nextLink, $match);
        if (count($match) == 0)
            return '';
        return $match[0];
    }


}
