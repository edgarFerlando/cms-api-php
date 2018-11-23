<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Triangle extends Model {

	public $table = 'triangle';
	public $timestamps = false;

	protected $fillable = [
        'triangle_layer_id',
        'step_1',
        'step_2',
        'step_3',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at'
    ];

    /*public function asset()
    {
    	return $this->hasOne('App\Taxonomy', 'id', 'step_2');//->where('step_1', '=', 'taxo_wallet_asset');
    }*/

    public function taxo_wallet_asset()
    {
    	return $this->hasOne('App\Taxonomy', 'id', 'step_2');//->where('step_1', '=', 'taxo_wallet_asset');
    }
    
    public function layer()
    {
    	return $this->hasOne('App\Models\TriangleLayer', 'id', 'triangle_layer_id');//->where('step_1', '=', 'taxo_wallet_asset');
    }
}
