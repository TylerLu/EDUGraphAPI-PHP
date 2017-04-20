<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Services;
use App\Model\Organizations;

class OrganizationsService
{
    public function CreateOrganization($tenant, $tenantId)
    {
        $org = Organizations::where('tenantId',$tenantId)->first();
        if(!$org){
            $org = new Organizations();
            $org->name = $tenant[0]->getDisplayName();
            $org->tenantId = $tenantId;
            $org->isAdminConsented =false;
            $org->created = date("Y-m-d h:i:s");
            $org->save();
        }
        return $org->id;
    }

    /**
     * Update isAdminConsented column.
     * @param $tenantId
     * @param $isConstented
     */
    public  function  SetTenantConsentResult($tenantId,$isConstented)
    {
        $org = Organizations::where('tenantId',$tenantId)->first();
        if($org){
            $org->isAdminConsented =$isConstented;
            $org->save();
        }
     }

    public function GetOrganization($tenantId)
    {
        return Organizations::where('tenantId',$tenantId)->first();
    }

    public function GetOrganizationId($tenantId){
        $org = $this->GetOrganization($tenantId);
        if($org){
            return $org->id;
        }
        return null;
    }
}