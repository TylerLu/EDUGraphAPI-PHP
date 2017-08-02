<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Services;

use App\Model\ClassroomSeatingArrangements;
use App\User;
use Illuminate\Support\Facades\Auth;

class UserService
{
    /**
     * Get the seat position of a user in a class
     *
     * @param string $o365UserId The Office 365 User Id of the user
     * @param string $classId The object id of the class
     *
     * @return int The seat position of the user in the class
     */
    public  function getSeatPositionInClass($o365UserId, $classId)
    {
        $seat = ClassroomSeatingArrangements::where([
            ['o365UserId', $o365UserId],
            ['classId', $classId]
        ])->first();
        return $seat ? $seat->position : 0;
    }

    /**
     * Save the seat arrangements
     *
     * @param array $arrangements The seat arrangements
     *
     * @return bool whether the saving succeeded
     */
    public function saveSeatingArrangements($arrangements)
    {
        if (!is_array($arrangements) || empty($arrangements)) {
            return false;
        }
        try {
            foreach ($arrangements as $arrangement) {
                $seat = ClassroomSeatingArrangements::where([
                    ['o365UserId', $arrangement['o365UserId']],
                    ['classid', $arrangement['classId']]
                ])->first();
                if ($seat) {
                    if ($arrangement['position'] != 0) {
                        $seat->position = $arrangement['position'];
                        $seat->save();
                    } else {
                        $seat->delete();
                    }
                } else if ($arrangement['position'] != 0) {
                    $seat = new ClassroomSeatingArrangements();
                    $seat->fill($arrangement);
                    $seat->save();
                }
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Get the favorite color of a user
     *
     * @param string $o365UserId The Office 365 User Id of the user
     *
     * @return string The favorite color of the user
     */
    public function getFavoriteColor($o365UserId)
    {
        $user = User::where('o365UserId', $o365UserId)->first();
        return $user ? $user->favorite_color : "";
    }

    /**
     * Update current login user information.
     * @param $o365UserId
     * @param $o365Email
     * @param $givenName
     * @param $surname
     * @param $orgId
     */
    public function saveCurrentLoginUserInfo($o365UserId, $o365Email, $givenName, $surname, $orgId)
    {
        $localUser = Auth::user();
        $this->setUserInfo($localUser,$o365UserId,$o365Email,$givenName,$surname,$orgId,null,null);
    }

    /**
     * Return all users of current organization
     * @param $orgId
     * @return mixed
     */
    public function getUsers($orgId)
    {
        return User::where('OrganizationId', $orgId)
            ->where('o365UserId', '!=', null)
            ->where('o365UserId', '!=', '')->get();
    }

    public function getUserById($userId)
    {
        return User::where('id', $userId)->first();
    }

    public function unlinkUser($userId)
    {
        $user = User::where('id', $userId)->first();
        if (!$user)
            return redirect('/admin/linkedaccounts');
        $user->o365Email = null;
        $user->o365UserId = null;
        $user->save();
    }

    public function unlinkAllUsers($orgId)
    {
        $users = User::where('OrganizationId', $orgId)->get();
        foreach ($users as $user) {
            $user->o365Email = null;
            $user->o365UserId = null;
            $user->save();
        }
    }


    public function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public function create($o365UserId,$o365Email,$firstName,$lastName,$organizationId,$favorite_color,$email)
    {
        $user = new User();
        $this->setUserInfo($user,$o365UserId,$o365Email,$firstName,$lastName,$organizationId,$favorite_color,$email);
        return $user;
    }

    public function saveUserInfoByEmail($o365UserId,$o365Email,$firstName,$lastName,$organizationId)
    {
        $user = $this->getUserByEmail($o365Email);
        if($user){
            $this->setUserInfo($user,$o365UserId,$o365Email,$firstName,$lastName,$organizationId,null,null);
        }
    }

    private function setUserInfo($user,$o365UserId,$o365Email,$firstName,$lastName,$organizationId,$favorite_color,$email)
    {
        if(isset($o365UserId))
            $user->o365UserId=$o365UserId;
        if(isset($o365Email))
            $user->o365Email=$o365Email;
        if(isset($firstName))
            $user->firstName = $firstName;
        if(isset($lastName))
            $user->lastName = $lastName;
        $user->password = '';
        if(isset($organizationId))
            $user->OrganizationId =$organizationId;
        if(isset($favorite_color))
            $user->favorite_color   =$favorite_color;
        if(isset($email))
            $user->email=$email;
        $user->save();
    }
}