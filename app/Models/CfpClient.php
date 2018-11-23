<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CfpClient extends Model {

	public $table = 'cfp_clients';
	public $timestamps = false;

	protected $primaryKey = 'internalid';
	protected $fillable = ['client_id', 'cfp_id', 'notes', 'created_by', 'created_at', 'updated_by', 'updated_at', 'record_flag'];

	public function userCfp() {
        return $this->hasOne('App\User', 'id', 'cfp_id');
    }
    
	public function userCustomers() {
        return $this->hasMany('App\User', 'id', 'client_id');
    }

    /*public function cfpByClientID() {
        return $this->hasOne('App\User', 'id', 'client_id');
    }

    public function clientsByCFPID() {
        return $this->hasMany('App\User', 'id', 'client_id');
    }*/

    public function client() {
        return $this->hasOne('App\User', 'id', 'client_id');
    }

    public function cfp() {
        return $this->hasOne('App\User', 'id', 'cfp_id');
    }

    public function usermetas() {
        return $this->hasMany('App\Models\UserMeta', 'user_id', 'client_id');
    }
}
