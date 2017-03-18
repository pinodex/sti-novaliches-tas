<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $group = DB::table('groups')->where('name', 'Administrators')->first();

        DB::table('users')->insert([
            'group_id'      => $group ? $group->id : null,
            'name'          => 'Admin',
            'username'      => 'admin',
            'email'         => 'admin@localhost',
            'password'      => bcrypt('admin')
        ]);
    }
}
