<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRequestsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('requests', function(Blueprint $table)
		{
			$table->char('id', 36)->primary();
			$table->integer('requestor_id');
			$table->integer('approver_id');
			$table->string('type');
			$table->dateTime('from_date');
			$table->dateTime('to_date');
			$table->float('incurred_balance', 10, 0);
			$table->text('reason', 65535);
			$table->tinyInteger('status')->nullable()->default(-1);
			$table->text('disapproval_reason', 65535)->nullable();
			$table->timestamp('responded_at')->nullable();
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
		Schema::drop('requests');
	}

}
