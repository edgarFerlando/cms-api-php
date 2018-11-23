<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goal extends Model {

	public $table = 'goals';
	public $timestamps = false;

	//protected $primaryKey = 'id';
	protected $fillable = ['goal_name', 'icon_path', 'thumb_path', 'position_under_grade_id'
							, 'created_by'
							, 'created_at'
							, 'updated_by'
							, 'updated_at'
							, 'record_flag'];

	public function getthumbPathAttribute($thumb_path)
    {
        return url('/').$thumb_path;  
    }	

    public function geticonPathAttribute($thumb_path)
    {
        return url('/').$thumb_path;  
    }	

	public function grades() {
        return $this->hasMany('App\Models\Grade', 'id', 'position_under_grade_id');
    }

    public function createdBy(){
    	return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function updatedBy(){
    	return $this->hasOne('App\User', 'id', 'updated_by');
    }

}
