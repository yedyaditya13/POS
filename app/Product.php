<?php

namespace App;
use app\Category;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    public function category() {
        return $this->belongsTo(Category::class);
    }
}

