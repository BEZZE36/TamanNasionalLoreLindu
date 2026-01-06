<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Step 1: Get the super-admin role ID
        $superAdminRole = DB::table('roles')->where('slug', 'super-admin')->first();

        if (!$superAdminRole) {
            // Create super-admin role if it doesn't exist
            $superAdminRoleId = DB::table('roles')->insertGetId([
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Full access to all features',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $superAdminRoleId = $superAdminRole->id;
        }

        // Step 2: Get all role IDs that are NOT super-admin
        $rolesToDelete = DB::table('roles')
            ->where('slug', '!=', 'super-admin')
            ->pluck('id')
            ->toArray();

        if (!empty($rolesToDelete)) {
            // Step 3: Move all admins with other roles to super-admin in pivot table
            DB::table('admin_role')
                ->whereIn('role_id', $rolesToDelete)
                ->update(['role_id' => $superAdminRoleId]);

            // Step 4: Remove duplicate entries in admin_role (keep only one super-admin per admin)
            $duplicates = DB::table('admin_role')
                ->select('admin_id', DB::raw('COUNT(*) as count'))
                ->groupBy('admin_id')
                ->having('count', '>', 1)
                ->pluck('admin_id');

            foreach ($duplicates as $adminId) {
                // Keep only one entry per admin
                $firstEntry = DB::table('admin_role')
                    ->where('admin_id', $adminId)
                    ->where('role_id', $superAdminRoleId)
                    ->first();

                if ($firstEntry) {
                    DB::table('admin_role')
                        ->where('admin_id', $adminId)
                        ->where('role_id', '!=', $superAdminRoleId)
                        ->delete();
                }
            }

            // Step 5: Delete role_permission entries for roles being deleted
            DB::table('role_permission')
                ->whereIn('role_id', $rolesToDelete)
                ->delete();

            // Step 6: Delete the other roles
            DB::table('roles')
                ->whereIn('id', $rolesToDelete)
                ->delete();
        }

        // Step 7: Update all admins to have role = 'super_admin' in the admins table
        DB::table('admins')
            ->whereNotIn('role', ['super_admin'])
            ->update(['role' => 'super_admin']);
    }

    public function down(): void
    {
        // Recreate the deleted roles
        $roles = [
            ['name' => 'Administrator', 'slug' => 'admin', 'description' => 'Standard admin access', 'is_active' => true],
            ['name' => 'Operator', 'slug' => 'operator', 'description' => 'Limited operational access', 'is_active' => true],
            ['name' => 'Viewer', 'slug' => 'viewer', 'description' => 'Read-only access', 'is_active' => true],
        ];

        foreach ($roles as $role) {
            $exists = DB::table('roles')->where('slug', $role['slug'])->exists();
            if (!$exists) {
                DB::table('roles')->insert(array_merge($role, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }
};
