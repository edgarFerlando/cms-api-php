<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanProtection extends Model {

	public $table = 'plan_protections';
	public $timestamps = false;

	protected $fillable = [
        'user_id', 
        'pendapatan_pensiun', 
        'inflasi_pertahun', 
        'bunga_deposito',
        'kebutuhan_dana',
        'durasi_proteksi',
        'kebutuhan_nilai_pertanggungan',
        'version',
        'plan_type',
        'plan_id',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at'
    ];
}
