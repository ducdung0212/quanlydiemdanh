<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // ensure there is a single admin account and update its email/password if needed
        User::updateOrCreate(
            ['role' => 'admin'],
            [
                'name' => 'Admin',
                'email' => 'buihoangducdung04@gmail.com',
                'password' => Hash::make('123456'),
                'role' => 'admin',
            ]
        );
    //viết thêm middleware redirect để khi login thì admin tự vào /admin, lecturer tự vào /lecturer luôn 
    }
}
