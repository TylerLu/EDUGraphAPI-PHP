<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('o365UserId')->nullable();
            $table->string('o365Email')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('salt')->nullable();
            $table->string('favorite_color')->nullable();
            $table->integer('OrganizationId')->nullable();
            $table->string('JobTitle')->nullable();
            $table->string('Department')->nullable();
            $table->string('MobilePhone')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('UserRoles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->uuid('UserId')->nullable();
        });

        Schema::create('TokenCache', function (Blueprint $table) {
            $table->increments('id');
            $table->text('refreshToken')->nullable();
            $table->string('UserId')->nullable();
            $table->text('accessTokens')->nullable();
        });

        Schema::create('Organizations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('tenantId')->nullable();
            $table->integer('isAdminConsented');
            $table->string('created')->nullable();
            $table->timestamps();
        });

        Schema::create('ClassroomSeatingArrangements', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('position')->nullable();
            $table->string('o365UserId')->nullable();
            $table->string('classId')->nullable();
        });

        Schema::create('DataSyncRecords', function (Blueprint $table) {
            $table->increments('id');
            $table->text('TenantId')->nullable();
            $table->text('Query')->nullable();
            $table->text('DeltaLink')->nullable();
            $table->dateTime('Updated')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
