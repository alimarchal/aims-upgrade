<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {

        // Administrator user
        $administrator = User::updateOrCreate(['id' => 13], [
            'name' => 'Sardar Asim Zaib',
            'email' => 'asardarasam22@gmail.com',
            'password' => Hash::make('password'),
            'department_id' => null,
            'status' => true,
            'profile_photo_path' => null,
        ]);
        $administrator->syncRoles(['Administrator']);

        // Super-Admin user
        $admin = User::updateOrCreate(['id' => 2], [
            'name' => 'Ali Raza Marchal (SA)',
            'email' => 'kh.marchal@gmail.com',
            'password' => Hash::make('Ali@03008169924'),
            'department_id' => null,
            'status' => true,
            'profile_photo_path' => null,
        ]);
        $admin->syncRoles(['Super-Admin']);


        // Front Desk users
        $frontDesk_1 = User::updateOrCreate(['id' => 1], [
            'name' => 'Junaid Naqvi',
            'email' => 'junaidnaqvi@aims.com',
            'password' => Hash::make('password'),
            'department_id' => null,
            'status' => true,
            'profile_photo_path' => null,
        ]);
        $frontDesk_1->syncRoles(['Front Desk/Receptionist']);

        $frontDesk_2 = User::updateOrCreate(['id' => 3], [
            'name' => 'Muhammad usman',
            'email' => 'usman@aims.com',
            'password' => Hash::make('password'),
            'department_id' => null,
            'status' => true,
            'profile_photo_path' => null,
        ]);
        $frontDesk_2->syncRoles(['Front Desk/Receptionist']);

        $frontDesk_3 = User::updateOrCreate(['id' => 4], [
            'name' => 'Faria',
            'email' => 'faria@aims.com',
            'password' => Hash::make('password'),
            'department_id' => null,
            'status' => true,
            'profile_photo_path' => null,
        ]);
        $frontDesk_3->syncRoles(['Front Desk/Receptionist']);

        $frontDesk_4 = User::updateOrCreate(['id' => 5], [
            'name' => 'Hamza Khan',
            'email' => 'Hamza@aims.com',
            'password' => Hash::make('password'),
            'department_id' => null,
            'status' => true,
            'profile_photo_path' => null,
        ]);
        $frontDesk_4->syncRoles(['Front Desk/Receptionist']);

        $frontDesk_5 = User::updateOrCreate(['id' => 6], [
            'name' => 'Raja Zulqarnain',
            'email' => 'Zulqarnain@aims.com',
            'password' => Hash::make('password'),
            'department_id' => null,
            'status' => true,
            'profile_photo_path' => null,
        ]);
        $frontDesk_5->syncRoles(['Front Desk/Receptionist']);

        $frontDesk_6 = User::updateOrCreate(['id' => 7], [
            'name' => 'IRFAN',
            'email' => 'irfan@aims.com',
            'password' => Hash::make('password'),
            'department_id' => null,
            'status' => true,
            'profile_photo_path' => null,
        ]);
        $frontDesk_6->syncRoles(['Front Desk/Receptionist']);

        $frontDesk_7 = User::updateOrCreate(['id' => 8], [
            'name' => 'Adnan',
            'email' => 'adnan@aims.com',
            'password' => Hash::make('password'),
            'department_id' => null,
            'status' => true,
            'profile_photo_path' => null,
        ]);
        $frontDesk_7->syncRoles(['Front Desk/Receptionist']);

        $frontDesk_8 = User::updateOrCreate(['id' => 9], [
            'name' => 'Farukh',
            'email' => 'FARUKH41@GMAIL.COM',
            'password' => Hash::make('password'),
            'department_id' => null,
            'status' => true,
            'profile_photo_path' => null,
        ]);
        $frontDesk_8->syncRoles(['Front Desk/Receptionist']);

        $frontDesk_9 = User::updateOrCreate(['id' => 10], [
            'name' => 'Saghar Kazmi',
            'email' => 'sagharkazmi@aims.com',
            'password' => Hash::make('password'),
            'department_id' => null,
            'status' => true,
            'profile_photo_path' => null,
        ]);
        $frontDesk_9->syncRoles(['Front Desk/Receptionist']);

        $frontDesk_10 = User::updateOrCreate(['id' => 11], [
            'name' => 'Ali Awan',
            'email' => 'Ali@aims.com',
            'password' => Hash::make('password'),
            'department_id' => null,
            'status' => true,
            'profile_photo_path' => null,
        ]);
        $frontDesk_10->syncRoles(['Front Desk/Receptionist']);

        $frontDesk_11 = User::updateOrCreate(['id' => 12], [
            'name' => 'Shoaib',
            'email' => 'Shoaib@aims.com',
            'password' => Hash::make('password'),
            'department_id' => null,
            'status' => true,
            'profile_photo_path' => null,
        ]);
        $frontDesk_11->syncRoles(['Front Desk/Receptionist']);

        $frontDesk_12 = User::updateOrCreate(['id' => 14], [
            'name' => 'Asifa Bukhari',
            'email' => 'asifa@aims.com',
            'password' => Hash::make('password'),
            'department_id' => null,
            'status' => true,
            'profile_photo_path' => null,
        ]);
        $frontDesk_12->syncRoles(['Front Desk/Receptionist']);

        $frontDesk_13 = User::updateOrCreate(['id' => 15], [
            'name' => 'auditor',
            'email' => 'auditor@aims.com',
            'password' => Hash::make('password'),
            'department_id' => null,
            'status' => true,
            'profile_photo_path' => null,
        ]);
        $frontDesk_13->syncRoles(['Front Desk/Receptionist']);

        $frontDesk_14 = User::updateOrCreate(['id' => 16], [
            'name' => 'Muhammad Arif',
            'email' => 'Arif@aims.com',
            'password' => Hash::make('password'),
            'department_id' => null,
            'status' => true,
            'profile_photo_path' => null,
        ]);
        $frontDesk_14->syncRoles(['Front Desk/Receptionist']);

        $frontDesk_15 = User::updateOrCreate(['id' => 17], [
            'name' => 'Tariq',
            'email' => 'Tariq@AIMS.COM',
            'password' => Hash::make('password'),
            'department_id' => null,
            'status' => true,
            'profile_photo_path' => null,
        ]);
        $frontDesk_15->syncRoles(['Front Desk/Receptionist']);

        $frontDesk_16 = User::updateOrCreate(['id' => 18], [
            'name' => 'Akabir',
            'email' => 'Akabir@aims.com',
            'password' => Hash::make('password'),
            'department_id' => null,
            'status' => true,
            'profile_photo_path' => null,
        ]);
        $frontDesk_16->syncRoles(['Front Desk/Receptionist']);

        $frontDesk_17 = User::updateOrCreate(['id' => 19], [
            'name' => 'Adeel Abbasi',
            'email' => 'Adeel@aims.com',
            'password' => Hash::make('password'),
            'department_id' => null,
            'status' => true,
            'profile_photo_path' => null,
        ]);
        $frontDesk_17->syncRoles(['Front Desk/Receptionist']);

        $frontDesk_18 = User::updateOrCreate(['id' => 20], [
            'name' => 'ASHBA',
            'email' => 'ASHBA@GMAIL.COM',
            'password' => Hash::make('password'),
            'department_id' => null,
            'status' => true,
            'profile_photo_path' => null,
        ]);
        $frontDesk_18->syncRoles(['Front Desk/Receptionist']);

        $frontDesk_19 = User::updateOrCreate(['id' => 21], [
            'name' => 'Muhammad Nasir',
            'email' => 'nasir@aims.com',
            'password' => Hash::make('password'),
            'department_id' => null,
            'status' => true,
            'profile_photo_path' => null,
        ]);
        $frontDesk_19->syncRoles(['Front Desk/Receptionist']);

    }
}
