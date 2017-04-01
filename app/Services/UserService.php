<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Services;

use App\Model\ClassroomSeatingArrangements;
use App\User;

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
    public static function getSeatPositionInClass($o365UserId, $classId)
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
    public static function saveSeatingArrangements($arrangements)
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
    public static function getFavoriteColor($o365UserId)
    {
        $user = User::where('o365UserId', $o365UserId)->first();
        return $user ? $user->favorite_color : "";
    }
}