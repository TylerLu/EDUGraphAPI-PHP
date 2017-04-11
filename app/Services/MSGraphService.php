<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Services;

use App\Config\SiteConstants;
use Exception;
use GuzzleHttp\Psr7\Stream;
use Illuminate\Support\Facades\Auth;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Conversation;
use Microsoft\Graph\Model\DriveItem;

class MSGraphService
{
    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->tokenCacheService = new TokenCacheService();
        $this->graph = new Graph();
    }

    /**
     *
     * Get the photo of a user
     *
     * @param string $o365UserId The Office 365 user id of the user
     *
     * @return Stream The photo of the user
     */
    public function getUserPhoto($o365UserId)
    {
        $token = $this->getToken();
        if ($token) {
            try {
                return $this->graph->setAccessToken($token)
                    ->createRequest("get", "/users/$o365UserId/photo/\$value")
                    ->setReturnType(Stream::class)
                    ->execute();
            } catch (Exception $e) {
                return null;
            }
        }
        return false;
    }

    /**
     *
     * Get all conversations of a group
     *
     * @param string $groupId The group id
     *
     * @return array All conversations of the group
     */
    public function getGroupConversations($groupId)
    {
        return $this->getAllPages("get", "/groups/$groupId/conversations", Conversation::class);
    }

    /**
     *
     * Get all drive items of a group
     *
     * @param string $groupId The group id
     *
     * @return array All drive items of the group
     */
    public function getGroupDriveItems($groupId)
    {
        return $this->getAllPages("get", "/groups/$groupId/drive/root/children", DriveItem::class);
    }

    /**
     *
     * Get the drive root of a group
     *
     * @param string $groupId The group id
     *
     * @return array The drive root of the group
     */
    public function getGroupDriveRoot($groupId)
    {
        return $this->getResponse("get", "/groups/$groupId/drive/root", DriveItem::class);
    }

    /**
     * Get all pages of data of MS Graph API
     *
     * @param string $requestType The HTTP method to use, e.g. "GET" or "POST"
     * @param string $endpoint The Graph endpoint to call
     * @param string $returnType The type of the return object or object of an array
     *
     * @return mixed All pages of data of MS Graph API
     */
    private function getAllPages($requestType, $endpoint, $returnType)
    {
        $pages = [];
        $token = $this->getToken();
        if ($token) {
            $request = $this->graph
                ->setAccessToken($token)
                ->createCollectionRequest($requestType, $endpoint)
                ->setReturnType($returnType);
            while (!$request->isEnd()) {
                $page = $request->getPage();
                if (is_array($page)) {
                    $pages = array_merge($pages, $page);
                }
            }
        }
        return $pages;
    }

    /**
     * Get data of MS Graph API
     *
     * @param string $requestType The HTTP method to use, e.g. "GET" or "POST"
     * @param string $endpoint The Graph endpoint to call
     * @param string $returnType The type of the return object or object of an array
     *
     * @return mixed data of MS Graph API
     */
    private function getResponse($requestType, $endpoint, $returnType)
    {
        $token = $this->getToken();
        if ($token) {
            return $this->graph
                ->setAccessToken($token)
                ->createRequest($requestType, $endpoint)
                ->setReturnType($returnType)
                ->execute();
        }
        return null;
    }

    /**
     * Get access token
     *
     * @return string The access token
     */
    private function getToken()
    {
        $user = Auth::user();
        $o365UserId = isset($user->o365UserId) ? $user->o365UserId : (array_key_exists(SiteConstants::Session_O365_User_ID, $_SESSION) ? $_SESSION[SiteConstants::Session_O365_User_ID] : null);
        if (!$o365UserId) {
            return null;
        }
        return $this->tokenCacheService->GetMsGraphToken($o365UserId);
    }

    private $tokenCacheService;
    private $graph;
}