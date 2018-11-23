<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model {

	public $table = 'grades';
	public $timestamps = false;

	//protected $primaryKey = 'internalid';
	protected $fillable = ['grade_name','ages','thumb_path','button_label'
							, 'created_by'
							, 'created_at'
							, 'updated_by'
							, 'updated_at'
							, 'record_flag'];

	public function getthumbPathAttribute($thumb_path)
    {
        return url('/').$thumb_path;  
    }

    public function goals() {
        return $this->hasMany('App\Models\Goal', 'position_under_grade_id', 'id');//->select(['goals.id', 'goal_name', 'icon_path', 'thumb_path']);
    }

    public function createdBy(){
    	return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function updatedBy(){
    	return $this->hasOne('App\User', 'id', 'updated_by');
    }
}
