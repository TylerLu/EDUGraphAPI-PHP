<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Lcobucci\JWT\Token;

class TokenCache extends Model
{
    public $timestamps=false;
    protected $table='TokenCache';

}
