<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Portofolio extends Model {

	public $table = 'mylife_mst_portofolio';
	public $timestamps = false;

	protected $fillable = ['portofolio_name', 'keterangan', 'created_by', 'created_on', 'updated_by', 'updated_on', 'record_flag'];

	public function portofolioDetails() {
        return $this->hasMany('App\Models\PortofolioDetail', 'portofolio_id', 'id');
    }

}	
