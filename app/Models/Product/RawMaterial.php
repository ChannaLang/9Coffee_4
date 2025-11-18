<?php

namespace App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    protected $fillable = [
        'name',
        'quantity',
        'unit'
    ];

public function variants()
{
    return $this->belongsToMany(\App\Models\Product\Variant::class, 'variant_raw_material', 'raw_material_id', 'variant_id')
                ->withPivot('quantity_required')
                ->withTimestamps();
}



}
