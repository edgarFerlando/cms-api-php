<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsuranceCoverage extends Model {

	public $table = 'insurance_coverages';
	public $timestamps = false;

	protected $fillable = [
        'user_id', 
        'taxo_insurance_type_id', 
        'nilai_pertanggungan',
        'premi_perbulan',
        'version',
        'plan_type',
        'plan_id',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at'
    ];
}
