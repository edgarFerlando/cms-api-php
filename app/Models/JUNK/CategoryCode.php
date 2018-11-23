<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryCode extends Model {

	public $table = 'mylife_mst_category';
	public $timestamps = false;

	protected $primaryKey = 'category_code';
	protected $fillable = ['category_name', 'keterangan', 'created_by', 'created_on', 'updated_by', 'updated_on', 'record_flag'];

}
