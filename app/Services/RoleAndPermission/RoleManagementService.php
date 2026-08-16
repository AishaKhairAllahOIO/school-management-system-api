<?php

namespace App\Services\RoleAndPermission;

use App\Models\Role;
use App\Models\SystemModule;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class RoleManagementService
{
    public function getRolesWithStatistics(): Collection
    {
        $modules = SystemModule::withCount('permissions as total_perms')->get();
        $roles = Role::with('permissions')->get();

        foreach ($roles as $role) {
            $fullAccessCount = 0;
            $limitedAccessCount = 0;

            $rolePermsGrouped = $role->permissions->groupBy('module_id');

            foreach ($modules as $module) {
                $ownedPermsCount = isset($rolePermsGrouped[$module->id]) ? $rolePermsGrouped[$module->id]->count() : 0;

                if ($ownedPermsCount > 0) {
                    if ($ownedPermsCount === $module->total_perms) {
                        $fullAccessCount++; 
                    } else {
                        $limitedAccessCount++; 
                    }
                }
            }

            $role->statistics = [
                'total_permissions'   => $role->permissions->count(),
                'module_access_count' => $fullAccessCount + $limitedAccessCount,
                'full_access_count'   => $fullAccessCount,
                'limited_access_count'=> $limitedAccessCount,
            ];
        }

        return $roles;
    }

    public function getSystemModules(): Collection
    {
        return SystemModule::with('permissions')->get();
    }

    public function syncPermissions(int $roleId, array $permissionIds)
    {
        $role = Role::findOrFail($roleId);

        if ($role->name === 'super_admin') {
            throw new Exception('Cannot modify permissions of the core super admin role.', 403);
        }

        $role->syncPermissions($permissionIds);
        return $role->permissions()->pluck('name');
    }
}