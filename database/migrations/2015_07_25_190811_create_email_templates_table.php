<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailTemplatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

		Schema::create('email_template_modules', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';
			$table->increments('id');
			$table->string('name', 255);
			$table->string('available_variables', 255);
            $table->timestamps();
		});

		Schema::create('email_templates', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';
			$table->increments('id');
			$table->integer('email_template_module_id')->unsigned();
            $table->timestamps();
            $table->foreign('email_template_module_id')->references('id')->on('email_template_modules');
		});

		Schema::create('email_template_translations', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';
			$table->increments('id');
			$table->integer('email_template_id')->unsigned();
			$table->string('locale')->index();
            $table->text('subject');
            $table->text('body');
            $table->unique(['email_template_id','locale']);
            $table->foreign('email_template_id')->references('id')->on('email_templates')->onDelete('cascade');
		});

		

		Schema::create('email_template_module_template', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';
			$table->string('cc', 255);
			$table->integer('email_template_module_id')->unsigned();
			$table->integer('email_template_id')->unsigned();
            $table->foreign('email_template_id')->references('id')->on('email_templates');
            $table->foreign('email_template_module_id')->references('id')->on('email_template_modules');
		});

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('email_template_translations');
		Schema::drop('email_templates');
		Schema::drop('email_template_modules');
		Schema::drop('email_template_module_template');
	}

}
