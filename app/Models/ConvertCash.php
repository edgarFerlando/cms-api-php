<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConvertCash extends Model {

	public $table = 'cash';
	public $timestamps = false;

	protected $fillable = [
        'client_id',
        'jumlah',
        'catatan',
        'version',
        'asset_repayment_id',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'record_flag'
    ];

}
