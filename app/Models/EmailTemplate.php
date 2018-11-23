<?php namespace App\Models;

use App\BaseModel;

class EmailTemplate extends BaseModel {

    public $table = 'email_templates';
    //public $translatedAttributes = [ 'subject', 'body' ];
    protected $fillable = [ 
        'email_template_module_id', 
        'subject', 
        'body',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at'
     ];

    public function emailTemplateModule() {
        return $this->hasOne('App\Models\EmailTemplateModule', 'id', 'email_template_module_id');
    }

}