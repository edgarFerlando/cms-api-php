<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenusTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('menus', function ($table) {
			$table->engine = 'InnoDB';
		    $table->increments('id');
		    $table->integer('menu_group_id')->unsigned();
		    $table->string('type', 10);
		    $table->string('option', 255)->nullable();
		    $table->boolean('is_published')->default(true);
		    
		    // Nested Set related fields
			$table->integer('parent_id')->nullable();
			$table->integer('lft')->nullable();
			$table->integer('rgt')->nullable();
			$table->integer('depth')->nullable();
			$table->integer('order')->nullable();

			// Indexes
		 	$table->index('parent_id');
			$table->index('lft');
			$table->index('rgt');
			$table->timestamps();
	  	});

	  	Schema::create('menu_translations', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';
			$table->increments('id');
			$table->integer('menu_id')->unsigned();
			$table->string('locale')->index();
            $table->string('title', 255);
            $table->string('url', 255);
            $table->unique(['menu_id','locale']);
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('menu_translations');
		Schema::drop('menus');
	}

}
