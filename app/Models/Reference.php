<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reference extends Model {

	public $table = 'references';
	public $timestamps = false;

	//protected $primaryKey = 'internalid';
	protected $fillable = [
        'code',
        'name',
        'company',
        'email',
        'phone',
		'created_by',
		'created_at',
		'updated_by',
		'updated_at',
		'record_flag'];

	public function getthumbPathAttribute($thumb_path)
    {
        return url('/').$thumb_path;  
    }

    public function createdBy(){
    	return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function updatedBy(){
    	return $this->hasOne('App\User', 'id', 'updated_by');
    }
}
