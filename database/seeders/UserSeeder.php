<?php

namespace Database\Seeders;

use App\Enums\ROLE;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        if (env('APP_ENV') === 'prod') {
            // Admin user
            $admin = User::firstOrCreate(
                ['email' => 'admin@evently.com'],
                ['password' => bcrypt('fnFPB3TzGWTBoLA')]
            );
            $admin->assignRole(ROLE::ADMIN);

            // Organizer user
            $organizer = User::firstOrCreate(
                ['email' => 'organizer@evently.com'],
                ['password' => bcrypt('organizer123')]
            );
            $organizer->assignRole(ROLE::ORGANIZER);

            // Attendee user
            $attendee = User::firstOrCreate(
                ['email' => 'attendee@evently.com'],
                ['password' => bcrypt('attendee123')]
            );
            $attendee->assignRole(ROLE::ATTENDEE);
        } else {
            // Development environment users
            $admin = User::firstOrCreate(
                ['email' => 'admin@evently.com'],
                ['password' => bcrypt('admin')]
            );
            $admin->assignRole(ROLE::ADMIN);

            $organizer = User::firstOrCreate(
                ['email' => 'organizer@evently.com'],
                ['password' => bcrypt('organizer')]
            );
            $organizer->assignRole(ROLE::ORGANIZER);

            $attendee = User::firstOrCreate(
                ['email' => 'attendee@evently.com'],
                ['password' => bcrypt('attendee')]
            );
            $attendee->assignRole(ROLE::ATTENDEE);
        }
    }
}
