<?php namespace App;

use App\Interfaces\ModelInterface as ModelInterface;

use URL;
use Baum\Node;


class Menu extends Node implements ModelInterface {

    use \App\classes\TranslatableSupportBaum;

    public $table = 'menus';
    public $translatedAttributes = [ 'title', 'url' ];
    protected $fillable = [ 'menu_group_id', 'type', 'option', 'is_published', 'parent_id', 'lft', 'rgt', 'depth', 'order','title', 'slug', 'url' ];
    protected $appends = ['url'];

    public function setUrlAttribute($value) {

        $this->attributes['url'] = $value;
    }

    public function getUrlAttribute() {

        return "menu/" . $this->attributes['slug'];
    }
}
