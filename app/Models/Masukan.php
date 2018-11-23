<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Masukan extends Model {

	public $table = 'masukan';
	protected $fillable = [
		'user_id',
		'name',
		'email',
		'description',
		'created_at',
		'updated_at'
	];

}
