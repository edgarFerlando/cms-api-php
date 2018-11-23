<?php namespace App;

use App\Interfaces\ModelInterface as ModelInterface;
//use Lang;
class MenuGroup extends BaseModel implements ModelInterface {

    public $table = 'menu_groups';
    protected $fillable = [ 'title', 'description' ];
    protected $appends = ['url'];

    public function setUrlAttribute($value) {

        $this->attributes['url'] = $value;
    }

    public function getUrlAttribute() {

        return 'menu-group/'.$this->attributes['slug'];
    }
}