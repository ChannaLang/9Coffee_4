<?php

namespace App\Models\Product; // <-- add this

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
}
