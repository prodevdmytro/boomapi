<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = [
        'category_id','partner_name','address','postcodes','contact_name','email','telephone'
    ];

    public function category()
    {
        return $this->belongsTo('App\Category');
    }
}
