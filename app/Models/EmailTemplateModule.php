<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplateModule extends Model {

	protected $fillable = [ 'subject', 'body' ];

	public function emailTemplates() {
        return $this->hasMany('App\Models\EmailTemplate', 'email_template_module_id', 'id');
    }


}
