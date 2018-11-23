<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanNumberSeq extends Model {

	public $table = 'plan_number_seq';
	public $timestamps = false;

	protected $fillable = [
        'user_id', 
        'current_value'
    ];
}
