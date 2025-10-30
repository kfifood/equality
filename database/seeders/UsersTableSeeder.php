<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'username' => 'superadmin',
                'password' => Hash::make('superadmin123'),
                'rfid_code' => 'RFID001',
                'full_name' => 'Super Administrator',
                'role' => 'superadmin',
                'phone' => '081100000001',
                'department' => 'IT Department',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'rfid_code' => 'RFID002',
                'full_name' => 'System Administrator',
                'role' => 'admin',
                'phone' => '081100000002',
                'department' => 'Administration',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'manager',
                'password' => Hash::make('manager123'),
                'rfid_code' => 'RFID003',
                'full_name' => 'Project Manager',
                'role' => 'manager',
                'phone' => '081100000003',
                'department' => 'Operations',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'supervisor',
                'password' => Hash::make('supervisor123'),
                'rfid_code' => 'RFID004',
                'full_name' => 'Line Supervisor',
                'role' => 'supervisor',
                'phone' => '081100000004',
                'department' => 'Production',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'staff',
                'password' => Hash::make('staff123'),
                'rfid_code' => 'RFID005',
                'full_name' => 'Staff Technician',
                'role' => 'staff',
                'phone' => '081100000005',
                'department' => 'Maintenance',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
