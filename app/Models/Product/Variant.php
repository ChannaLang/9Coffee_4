<?php

namespace App\Models\Product;
use App\Models\Product\RawMaterial;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    use HasFactory;

    protected $table = "variants";

    protected $fillable = [
        'product_id',
        'name',   // e.g., Small, Large, Beef, Chicken
        'price',  // price for this variant
    ];

    // Variant belongs to a Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Variant has many raw materials
    public function rawMaterials()
    {
        return $this->belongsToMany(
            RawMaterial::class,  // now points to App\Models\Product\RawMaterial
            'variant_raw_material',
            'variant_id',
            'raw_material_id'
        )->withPivot('quantity_required')
        ->withTimestamps();
    }

    // Deduct raw materials after sale
    public function deductIngredients(int $qty)
    {
        foreach ($this->rawMaterials as $material) {
            $requiredQty = $material->pivot->quantity_required * $qty;
            $material->decrement('quantity', $requiredQty);
        }
    }

    // Calculate available stock based on ingredients
    public function getAvailableStockAttribute()
    {
        if ($this->rawMaterials->isEmpty()) {
            return $this->product->quantity ?? 0;
        }

        $minStock = null;
        foreach ($this->rawMaterials as $material) {
            $possible = floor($material->quantity / $material->pivot->quantity_required);
            if ($minStock === null || $possible < $minStock) {
                $minStock = $possible;
            }
        }

        return $minStock ?? 0;
    }
}
