<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model {

	protected $fillable = ['code', 'name', 'visitors', 'created_at', 'update_at'];

}
