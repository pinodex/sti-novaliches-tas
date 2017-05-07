<?php

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
        DB::table('users')->insert([
            'group_id'                  => 1,
            'username'                  => 'admin',
            'password'                  => bcrypt('admin'),
            'first_name'                => 'Administrator',
            'require_password_change'   => 1
        ]);
    }
}
