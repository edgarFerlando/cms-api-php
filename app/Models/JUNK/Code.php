<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Code extends Model {

	public $table = 'mylife_mst_code';
	public $timestamps = false;

	protected $primaryKey = 'code';
	protected $fillable = ['code_name', 'category_code', 'keterangan', 'created_by', 'created_on', 'updated_by', 'updated_on', 'record_flag'];

	public function categoryCode() {
        return $this->hasOne('App\Models\CategoryCode')->where('category_code', '=', 'category_code');
    }

}
