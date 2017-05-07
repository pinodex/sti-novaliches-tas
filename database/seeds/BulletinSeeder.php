<?php

use Illuminate\Database\Seeder;

class BulletinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('bulletins')->insert([
            'author_id'     => 1,
            'title'         => 'Sample Bulletin Entry',
            'contents'      => 'This is a sample bulletin entry. Go to the bulletin admin page to edit bulletin entries.'
        ]);
    }
}
