<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CfpRating extends Model {

	public $table = 'cfp_ratings';
	public $timestamps = false;

	protected $fillable = [
        'cfp_id',
        'rating_stars',
        'comments',
        'client_id',
        'plan_analysis_version',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at',
        'record_flag'
    ];

}
