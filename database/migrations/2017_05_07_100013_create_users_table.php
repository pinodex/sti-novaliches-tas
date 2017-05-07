<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('group_id')->nullable();
			$table->integer('department_id')->nullable();
			$table->integer('profile_id')->nullable();
			$table->string('username')->unique('username_UNIQUE');
			$table->string('password', 60)->nullable();
			$table->string('first_name');
			$table->string('middle_name')->nullable();
			$table->string('last_name')->nullable();
			$table->string('email')->nullable()->unique('email_UNIQUE');
			$table->string('picture_path')->nullable();
			$table->string('thumbnail_path')->nullable();
			$table->boolean('require_password_change')->nullable()->default(0);
			$table->timestamp('last_login_at')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
