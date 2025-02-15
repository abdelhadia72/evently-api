<?php

namespace Database\Seeders\Permissions;

use App\Enums\ROLE as ROLE_ENUM;
use App\Models\Role;
use App\Services\ACLService;
use Illuminate\Database\Seeder;

class CrudPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(ACLService $aclService)
    {
        /*
            // Here, include project specific permissions. E.G.:
            $aclService->createScopePermissions('interests', ['create', 'read', 'update', 'delete', 'import', 'export']);
            $aclService->createScopePermissions('games', ['create', 'read', 'read_own', 'update', 'delete']);

            $adminRole = Role::where('name', ROLE_ENUM::ADMIN)->first();
            $aclService->assignScopePermissionsToRole($adminRole, 'interests', ['create', 'read', 'update', 'delete', 'import', 'export']);
            $aclService->assignScopePermissionsToRole($adminRole, 'games', ['create', 'read', 'read_own', 'update', 'delete']);

            $advertiserRole = Role::where('name', 'advertiser')->first();
            $aclService->assignScopePermissionsToRole($advertiserRole, 'interests', ['read']);
            $aclService->assignScopePermissionsToRole($advertiserRole, 'games', ['create', 'read_own']);
        */

        $aclService->createScopePermissions('events', ['create', 'read', 'read_own', 'update', 'update_own', 'delete', 'delete_own']);

        $adminRole = Role::where('name', ROLE_ENUM::ADMIN->value)->first();
        $organizerRole = Role::where('name', ROLE_ENUM::ORGANIZER->value)->first();

        $aclService->assignScopePermissionsToRole($adminRole, 'events', ['create', 'read', 'read_own', 'update', 'update_own', 'delete', 'delete_own']);

        $aclService->assignScopePermissionsToRole($organizerRole, 'events', ['create', 'read_own', 'update_own', 'delete_own']);
    }
}
