<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCodeCounter extends Model {

	public $table = 'user_code_counter';
	public $timestamps = false;

	protected $fillable = [
        'branch_code', 
        'date_code', 
        'last_number',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at'
    ];
}
