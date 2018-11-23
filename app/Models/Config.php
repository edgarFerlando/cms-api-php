<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Config extends Model {

	public $table = 'config';
	protected $fillable = [
		'key',
		'value',
		'group',
		'type',
		'modified_by',
		'modified_at'
	];

}
