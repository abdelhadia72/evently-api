<?php

namespace Database\Seeders\Permissions;

use App\Enums\ROLE as ROLE_ENUM;
use App\Models\Role;
use App\Services\ACLService;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    private ACLService $aclService;

    public function __construct(ACLService $aclService)
    {
        $this->aclService = $aclService;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create roles
        $adminRole = $this->aclService->createRole(ROLE_ENUM::ADMIN);
        $organizerRole = $this->aclService->createRole(ROLE_ENUM::ORGANIZER);
        $attendeeRole = $this->aclService->createRole(ROLE_ENUM::ATTENDEE);

        // Create permissions for different entities
        $this->aclService->createScopePermissions('users', ['create', 'read', 'update', 'delete']);
        $this->aclService->createScopePermissions('events', ['create', 'read', 'update', 'delete']);

        // Admin permissions (full access)
        $this->aclService->assignScopePermissionsToRole($adminRole, 'users', ['create', 'read', 'update', 'delete']);
        $this->aclService->assignScopePermissionsToRole($adminRole, 'events', ['create', 'read', 'update', 'delete']);

        // Organizer permissions
        $this->aclService->assignScopePermissionsToRole($organizerRole, 'events', ['create', 'read', 'update', 'delete']);
        $this->aclService->assignScopePermissionsToRole($organizerRole, 'users', ['read']);

        // Attendee permissions
        $this->aclService->assignScopePermissionsToRole($attendeeRole, 'events', ['read']);
        $this->aclService->assignScopePermissionsToRole($attendeeRole, 'users', ['read']);
    }

    public function rollback()
    {
        $roles = Role::whereIn('name', [
            ROLE_ENUM::ADMIN->value,
            ROLE_ENUM::ORGANIZER->value,
            ROLE_ENUM::ATTENDEE->value,
        ])->get();

        foreach ($roles as $role) {
            $this->aclService->removeScopePermissionsFromRole($role, 'users', ['create', 'read', 'update', 'delete']);
            $this->aclService->removeScopePermissionsFromRole($role, 'events', ['create', 'read', 'update', 'delete']);
        }
    }
}
