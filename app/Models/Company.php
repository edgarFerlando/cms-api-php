<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model {

	public $table = 'mylife_mst_company';
	public $timestamps = false;

	protected $primaryKey = 'company_code';
	protected $fillable = ['company_name', 'company_type', 'keterangan', 'created_by', 'created_on', 'updated_by', 'updated_on', 'record_flag'];

	public function code() {
        return $this->hasOne('App\Models\Code')->where('code', '=', 'company_type');
    }

}
