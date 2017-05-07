<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBulletinsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bulletins', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('author_id')->nullable();
			$table->integer('last_author_id')->nullable();
			$table->string('title')->nullable();
			$table->text('contents');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('bulletins');
	}

}
