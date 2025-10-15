<?php

namespace Database\Seeders;

use App\Enums\Role;
use Illuminate\Database\Seeder;
use App\Enums\Permission as P;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        // 1) Alle permissions aanmaken (idempotent)
        foreach (P::cases() as $perm) {
            SpatiePermission::firstOrCreate(
                ['name' => $perm->value, 'guard_name' => $guard]
            );
        }

        // 2) Rollen aanmaken
        $allRoles = [
            Role::Guest, Role::Unverified, Role::User, Role::Author,
            Role::Editor, Role::Moderator, Role::Admin, Role::SuperAdmin,
        ];
        foreach ($allRoles as $r) {
            SpatieRole::firstOrCreate(['name' => $r->value, 'guard_name' => $guard]);
        }

        // 3) Basisrechten per rol (increment/erfenis)
        $base = [
            Role::Guest->value => [
                P::PostsRead,
            ],
            Role::Unverified->value => [
                // erft Guest +
                P::PostsCreate,              // mag posten, maar we laten policies/moderation bepalen of direct live gaat
                P::ProfileUpdateSelf,
            ],
            Role::User->value => [
                // erft Unverified +
                P::FriendshipsSend,
                P::MessagesSend,
                P::PostsUpdateOwn,
                P::PostsDeleteOwn,
                P::ProfileDeactivateSelf,
                P::ProfileDeleteSelf,
            ],
            Role::Author->value => [
                // erft User +
                // Auteur is maker; owner policies bepalen feitelijke rechten.
                // Hier geen extra perms nodig bovenop *.own, maar je kunt future extras toevoegen.
            ],
            Role::Editor->value => [
                // erft Author +
                P::PostsSetInactive,
                P::PostsReview,
                P::PostsFlag,               // kan ook escaleren
            ],
            Role::Moderator->value => [
                // erft Editor +
                P::PostsUpdateAny,
                P::PostsDeleteAny,
                P::PostsFlagsResolve,
                P::UsersNotify,
            ],
            Role::Admin->value => [
                // erft Moderator +
                P::AdminView,
                P::AdminManage,
                P::RolesAssign,
                P::RolesManage,
                P::AdminsCreate,
                P::AdminsDeleteSelfOrLower,
                P::UsersView,
                P::UsersCreate,
                P::UsersUpdate,
                P::UsersDeactivate,
                P::UsersSuspend,
                P::UsersDeleteSoft,
            ],
            Role::SuperAdmin->value => [
                // erft Admin + mag superadmin transfer (en Gate::before geeft verder alles)
                P::SuperAdminTransfer,
            ],
        ];

        // 4) Rol-erfenis toepassen (accumulatief)
        $ordered = array_map(fn($r) => $r->value, $allRoles); // in volgorde van hierboven
        $cumulative = [];
        foreach ($ordered as $i => $roleName) {
            $own = $base[$roleName] ?? [];
            $prev = $i > 0 ? ($cumulative[$ordered[$i-1]] ?? []) : [];
            $cumulative[$roleName] = $this->normalize(array_merge($prev, $own));
        }

        // 5) Sync permissions per rol
        foreach ($cumulative as $roleName => $perms) {
            /** @var \Spatie\Permission\Models\Role $role */
            $role = SpatieRole::where('name', $roleName)->first();
            $role?->syncPermissions(array_map(fn($p) => $p->value, $perms));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->command?->info('Roles & permissions gesynchroniseerd.');
    }

    /** @return array<int, \App\Enums\Permission> */
    private function normalize(array $perms): array
    {
        // Maak unieke lijst Permission enums
        $out = [];
        foreach ($perms as $p) {
            $enum = $p instanceof P ? $p : P::from($p);
            $out[$enum->value] = $enum;
        }
        return array_values($out);
    }
}
