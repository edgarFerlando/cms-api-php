<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CfpCodeCounter extends Model {

	public $table = 'cfp_code_counter';
	public $timestamps = false;

	protected $fillable = [
        'branch_code', 
        'last_number',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at'
    ];
}
