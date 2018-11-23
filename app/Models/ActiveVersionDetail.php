<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActiveVersionDetail extends Model {

	public $table = 'active_version_details';
	public $timestamps = false;

	protected $fillable = [
        'user_id', 
        'version',
        'status',
        'active_version_key',
        'approved_by',
        'approved_at',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at',
        'reason_reject',
        'rejected_at',
        'rejected_by',
        'is_email_sent',
        'email_web_path'
    ];
}
