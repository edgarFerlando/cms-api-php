<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pages', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';
			$table->increments('id');
			$table->boolean('is_published')->default(true);
            $table->timestamps();
		});

		Schema::create('page_translations', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';
			$table->increments('id');
			$table->integer('page_id')->unsigned();
			$table->string('locale')->index();
            $table->string('title', 255);
            $table->string('slug')->nullable();
            $table->text('body');
            $table->string('meta_title', 255);
            $table->string('meta_keywords', 255);
            $table->string('meta_description', 255);
            $table->unique(['page_id','locale']);
            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('page_translations');
		Schema::drop('pages');
	}

}

