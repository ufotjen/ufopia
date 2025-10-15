<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL', 'super_admin@example.com');
        $password = env('SUPER_ADMIN_PASSWORD', 'password');
        $name = env('SUPER_ADMIN_NAME', 'super_admin');

        $superAdmins = User::role(Role::SuperAdmin->value)->get();

        if ($superAdmins->count() === 0) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles([Role::SuperAdmin->value]);
            $this->command?->warn("SuperAdmin aangemaakt: {$email}");
        } else {
            $this->command?->info("SuperAdmin(s) aanwezig: {$superAdmins->count()}");
        }
    }
}
