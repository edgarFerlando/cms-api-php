<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TriangleLayer extends Model {

	public $table = 'triangle_layers';
	public $timestamps = false;

	protected $fillable = [
        'title', 
        'description', 
        'stack_number',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at'
    ];

    public function layerDetails() {

        return $this->hasMany('App\Models\Triangle', 'triangle_layer_id', 'id');
    }

}
