<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'category_id','service_name','normal_price','boom_price','boomx_price'
    ];

    public function category()
    {
        return $this->belongsto('App\Category');
    }
    
}
