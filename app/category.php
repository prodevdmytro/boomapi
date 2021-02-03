<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'category_name','expires','postcode',
    ];

    public function service()
    {
        return $this->hasMany('App\Service');
    }

    public function boom()
    {
        return $this->hasMany('App\Boom');
    }

    public function partner()
    {
        return $this->hasMany('App\Partner');
    }
}
