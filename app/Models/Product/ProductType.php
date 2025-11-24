<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    protected $table = 'product_types';
    protected $fillable = ['name'];
    public $timestamps = false;

    public function products()
    {
        return $this->hasMany(\App\Models\Product\Product::class, 'product_type_id');
    }

    public function subTypes()
    {
        return $this->hasMany(\App\Models\Product\SubType::class, 'product_type_id');
    }
}

