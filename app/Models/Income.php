<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model {

	public $table = 'incomes';
	public $timestamps = false;
    //protected $primaryKey = 'user_id';

	protected $fillable = [
        'id',
        'user_id', 
        'pendapatan_bulanan', 
        'pendapatan_lain', 
        'pendapatan_tidak_tetap_bulan',
        'version',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at'
    ];

    /*public function expenses() {
        return $this->hasMany('App\Models\Expense', 'user_id', 'user_id')->where('version', 5);
    }

    public function user() {
        return $this->hasOne('App\User', 'id', 'user_id');
    }*/
}
