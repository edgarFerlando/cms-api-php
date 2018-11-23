<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoalGrade extends Model {

	public $table = 'goal_grade';
	public $timestamps = false;

	protected $primaryKey = 'user_id';
	protected $fillable = ['user_id', 'grade_id', 'goal_id'
							, 'created_by'
							, 'created_at'
							, 'updated_by'
							, 'updated_at'
							, 'record_flag'];

	public function goal(){
    	return $this->hasOne('App\Models\Goal', 'id', 'goal_id');
    }

    public function grade(){
    	return $this->hasOne('App\Models\Grade', 'id', 'grade_id');
    }

    public function createdBy(){
    	return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function updatedBy(){
    	return $this->hasOne('App\User', 'id', 'updated_by');
    }
}
