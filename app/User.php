<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App;

use App\Config\UserType;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    public $userType=UserType::Local;
    public $tenantId;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'favorite_color', 'firstName', 'lastName', 'o365UserId', 'o365Email'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function isLinked()
    {
        if($this->userType==UserType::O365)
            return false;
        return strlen($this->o365UserId) > 0 and strlen($this->o365Email) > 0;
    }



}