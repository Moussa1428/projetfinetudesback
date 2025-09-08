<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call(RoleUser::class);
        $user = User::create([
             'name'=> 'Moussa',
             'last_name'=>'Abakar',
             'email'=>'infolanguage.com@gmail.com',
             'phone'=> '772831802',
             'address'=> 'N\'Djamena, Tchad',
             'password'=> Hash::make('passer123'),
             'is_active'=> true,
             'remember_token' => Str::random(10),
         ]);
        $user->assignRole('Super_Admin');



    }
}
