<?php

namespace Database\Seeders;

use App\Enums\ROLE;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

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
            $admin = User::firstOrCreate(
                ['email' => 'admin@evently.com'],
                [
                    'password' => bcrypt('fnFPB3TzGWTBoLA'),
                    'is_verified' => true,
                    'verified_at' => Carbon::now(),
                    'email_verified_at' => Carbon::now(),
                    'login_attempts' => 0,
                    'last_login_at' => null,
                    'last_login_ip' => null,
                    'otp' => null,
                    'otp_expires_at' => null,
                ]
            );
            $admin->assignRole(ROLE::ADMIN);

            $organizer = User::firstOrCreate(
                ['email' => 'organizer@evently.com'],
                [
                    'password' => bcrypt('organizer123'),
                    'is_verified' => true,
                    'verified_at' => Carbon::now(),
                    'email_verified_at' => Carbon::now(),
                    'login_attempts' => 0,
                    'last_login_at' => null,
                    'last_login_ip' => null,
                    'otp' => null,
                    'otp_expires_at' => null,
                ]
            );
            $organizer->assignRole(ROLE::ORGANIZER);

            $attendee = User::firstOrCreate(
                ['email' => 'attendee@evently.com'],
                [
                    'password' => bcrypt('attendee123'),
                    'is_verified' => true,
                    'verified_at' => Carbon::now(),
                    'email_verified_at' => Carbon::now(),
                    'login_attempts' => 0,
                    'last_login_at' => null,
                    'last_login_ip' => null,
                    'otp' => null,
                    'otp_expires_at' => null,
                ]
            );
            $attendee->assignRole(ROLE::ATTENDEE);
        } else {
            $admin = User::firstOrCreate(
                ['email' => 'admin@evently.com'],
                [
                    'password' => bcrypt('admin'),
                    'is_verified' => true,
                    'verified_at' => Carbon::now(),
                    'email_verified_at' => Carbon::now(),
                    'login_attempts' => 0,
                    'last_login_at' => null,
                    'last_login_ip' => null,
                    'otp' => null,
                    'otp_expires_at' => null,
                ]
            );
            $admin->assignRole(ROLE::ADMIN);

            $organizer = User::firstOrCreate(
                ['email' => 'organizer@evently.com'],
                [
                    'password' => bcrypt('organizer'),
                    'is_verified' => true,
                    'verified_at' => Carbon::now(),
                    'email_verified_at' => Carbon::now(),
                    'login_attempts' => 0,
                    'last_login_at' => null,
                    'last_login_ip' => null,
                    'otp' => null,
                    'otp_expires_at' => null,
                ]
            );
            $organizer->assignRole(ROLE::ORGANIZER);

            $attendee = User::firstOrCreate(
                ['email' => 'attendee@evently.com'],
                [
                    'password' => bcrypt('attendee'),
                    'is_verified' => true,
                    'verified_at' => Carbon::now(),
                    'email_verified_at' => Carbon::now(),
                    'login_attempts' => 0,
                    'last_login_at' => null,
                    'last_login_ip' => null,
                    'otp' => null,
                    'otp_expires_at' => null,
                ]
            );
            $attendee->assignRole(ROLE::ATTENDEE);
        }
    }
}
