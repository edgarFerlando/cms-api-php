<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model {

	use SoftDeletes;

	public $table = 'banks';
	protected $primaryKey = 'id';
    protected $fillable = [
		'title',
		'slug',
		'featured_image',
        'is_status',
        'color',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
        'record_flag' 
    ];

}
