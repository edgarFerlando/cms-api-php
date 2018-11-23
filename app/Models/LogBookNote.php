<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogBookNote extends Model {

	public $table = 'log_book_notes';
	public $timestamps = false;

	protected $fillable = [
        'user_id', 
        'version', 
        'note', 
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at'
    ];
}
