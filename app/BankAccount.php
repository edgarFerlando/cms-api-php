<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
//use App\Interfaces\ModelInterface as ModelInterface;

class BankAccount extends Model{
    use SoftDeletes;

	public $table = 'bank_accounts';
    protected $fillable = ['deleted_by','deleted_at','record_flag'];
    protected $appends = [];
    protected $dates = ['deleted_at'];
}
