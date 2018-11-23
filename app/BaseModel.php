<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model {

	public function scopeSearch($query, $search){

        return $query->where(function($query) use ($search){

            $query->where('title', 'LIKE', "%$search%")
                    ->where('lang', getLang())
                    ->orWhere('content', 'LIKE', "%$search%");
        });
    }

}
