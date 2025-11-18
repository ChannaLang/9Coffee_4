<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class SubType extends Model
{
    protected $table = 'sub_types';
    protected $fillable = ['name', 'product_type_id'];
    public $timestamps = false;

    public function products()
    {
        return $this->hasMany(\App\Models\Product\Product::class, 'sub_type_id');
    }
}
