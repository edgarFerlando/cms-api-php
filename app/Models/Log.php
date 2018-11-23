<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model {

	public $table = 'log';
	protected $fillable = [
		'name',
		'description',
		'created_at',
		'updated_at'
	];
}
