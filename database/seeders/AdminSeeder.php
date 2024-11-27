<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;




class AdminSeeder extends Seeder
{

    public function run(): void
    {
        $admin_password = env('ADMIN_PASSWORD');
        $admins = [
            [
                'admin_name' => 'Carmel',
                'email' => 'zohot84@gmail.com',
                'role' => 'SuperAdmin',
                'receive_mails' => 'Yes',
            ],
            [
                'admin_name' => 'Kapil',
                'email' => 'kapil@socxo.com',
                'role' => 'SuperAdmin',
                'receive_mails' => 'No',
            ],
            [
                'admin_name' => 'Sudarsan Rao',
                'email' => 'sudarsan.rao@socxo.com',
                'role' => 'SuperAdmin',
                'receive_mails' => 'No',
            ],
            // [
            //     'admin_name' => 'Lindsay Smith',
            //     'email' => 'lindsay.smith@clearlink.com',
            //     'role' => 'SuperAdmin',
            //     'receive_mails' => 'No',
            // ],
            // [
            //     'admin_name' => 'Mandi Coleman',
            //     'email' => 'mandi.coleman@clearlink.com',
            //     'role' => 'Admin',
            //     'receive_mails' => 'No',
            // ]
        ];

        foreach ($admins as $admin) {
            if (!DB::table('admins')->where('email', $admin['email'])->exists()) {
                DB::table('admins')->insert([
                    'admin_name' => $admin['admin_name'],
                    'email' => $admin['email'],
                    'password' => Hash::make($admin_password),
                    'role' => $admin['role'],
                    'receive_mails' => $admin['receive_mails'],
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
