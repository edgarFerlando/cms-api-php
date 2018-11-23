<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortofolioDetail extends Model {
	
	public $table = 'mylife_mst_portofolio_detail';
	public $timestamps = false;

	protected $fillable = ['detail_name', 'portofolio_id', 'keterangan', 'created_by', 'created_on', 'updated_by', 'updated_on', 'record_flag'];

	public function portofolio() {
        return $this->hasOne('App\Models\Portofolio')->where('id', '=', 'portofolio_id');
    }

}
