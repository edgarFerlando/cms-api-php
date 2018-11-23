<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model {

	public $table = 'role_user';
	public $timestamps = false;

    public function role() {
        return $this->hasOne('App\Models\Role', 'id', 'role_id');
    }
}
