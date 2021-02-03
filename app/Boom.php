<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Boom extends Model
{
    protected $fillable = [
        'category_id', 'service_id','boom_id','boombtn_id','boombtn_id','expired_date'
    ];

    public function category()
    {
        return $this->belongsTo('App\Category');
    }
}
