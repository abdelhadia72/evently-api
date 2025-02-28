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
        $this->createRoles();
        $this->createPermissions();
        $this->assignPermissions();
    }

    private function createRoles()
    {
        $this->adminRole = $this->aclService->createRole(ROLE_ENUM::ADMIN);
        $this->organizerRole = $this->aclService->createRole(ROLE_ENUM::ORGANIZER);
        $this->attendeeRole = $this->aclService->createRole(ROLE_ENUM::ATTENDEE);
    }

    private function createPermissions()
    {
        // Define CRUD permissions for each entity
        $this->aclService->createScopePermissions('users', ['create', 'read', 'update', 'delete']);
        $this->aclService->createScopePermissions('events', ['create', 'read', 'update', 'delete']);
        $this->aclService->createScopePermissions('categories', ['create', 'read', 'update', 'delete']);
    }

    private function assignPermissions()
    {
        // Admin permissions
        $this->aclService->assignScopePermissionsToRole($this->adminRole, 'users', [
            'create',
            'read',
            'update',
            'delete',
        ]);
        $this->aclService->assignScopePermissionsToRole($this->adminRole, 'events', [
            'create',
            'read',
            'update',
            'delete',
        ]);
        $this->aclService->assignScopePermissionsToRole($this->adminRole, 'categories', [
            'create',
            'read',
            'update',
            'delete',
        ]);

        // Organizer permissions
        $this->aclService->assignScopePermissionsToRole($this->organizerRole, 'events', [
            'create',
            'read',
            'update',
            'delete',
        ]);
        $this->aclService->assignScopePermissionsToRole($this->organizerRole, 'users', ['read']);
        $this->aclService->assignScopePermissionsToRole($this->organizerRole, 'categories', ['read']);

        // Attendee permissions
        $this->aclService->assignScopePermissionsToRole($this->attendeeRole, 'events', ['read']);
        $this->aclService->assignScopePermissionsToRole($this->attendeeRole, 'users', ['read']);
        $this->aclService->assignScopePermissionsToRole($this->attendeeRole, 'categories', ['read']);
    }

    public function rollback()
    {
        $roles = Role::whereIn('name', [
            ROLE_ENUM::ADMIN->value,
            ROLE_ENUM::ORGANIZER->value,
            ROLE_ENUM::ATTENDEE->value,
        ])->get();

        foreach ($roles as $role) {
            $this->aclService->removeScopePermissionsFromRole($role, 'users', [
                'create',
                'read',
                'update',
                'delete',
            ]);
            $this->aclService->removeScopePermissionsFromRole($role, 'events', [
                'create',
                'read',
                'update',
                'delete',
            ]);
            $this->aclService->removeScopePermissionsFromRole($role, 'categories', [
                'create',
                'read',
                'update',
                'delete',
            ]);
        }
    }
}
