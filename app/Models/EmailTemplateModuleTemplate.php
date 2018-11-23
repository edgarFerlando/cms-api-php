<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplateModuleTemplate extends Model {

	public $table = 'email_template_module_template';
    protected $fillable = [ 'cc', 'email_template_module_id', 'email_template_id' ];

	public function emailTemplate() {
        return $this->hasOne('App\Models\EmailTemplate', 'id', 'email_template_id');
    }

    public function emailTemplateModule() {
        return $this->hasOne('App\Models\EmailTemplateModule', 'id', 'email_template_module_id');
    }
}
