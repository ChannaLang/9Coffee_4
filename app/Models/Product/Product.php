<?php

namespace App\Models\Product;

use App\Models\RawMaterial;
use App\Models\ProductType;
use App\Models\SubType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Product extends Model
{
    use HasFactory;

    protected $table = "products";

    protected $fillable = [
        "name",
        "image",
        "price",
        "description",
        "product_type_id",
        "sub_type_id",
        "quantity",
    ];

    public $timestamps = true; // keep timestamps for product created_at/updated_at

    // Product Type relationship
    public function type() {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    public function productType() {
        return $this->type(); // alias
    }

    // SubType relationship
    public function subType() {
        return $this->belongsTo(SubType::class, 'sub_type_id');
    }


// Raw Materials pivot relationship (with size support)
public function rawMaterials()
{
    return $this->belongsToMany(
        RawMaterial::class,          // related model
        'product_raw_material',      // pivot table
        'product_id',                // foreign key on pivot table for this model
        'raw_material_id'            // foreign key on pivot table for related model
    )
    ->withPivot('quantity_required', 'size')  // include size column
    ->withTimestamps();
}




    // Deduct ingredients after a product is sold
public function deductIngredients(int $qty, string $size)
{
    foreach ($this->rawMaterials->where('pivot.size', $size) as $raw) {
        $requiredQty = $raw->pivot->quantity_required * $qty;
        $raw->decrement('quantity', $requiredQty);
    }
}


    // Orders relationship
    public function orders()
    {
        return $this->hasMany(\App\Models\Product\Order::class);
    }

    // Available stock based on raw materials
    public function getAvailableStockAttribute()
    {
        if ($this->rawMaterials->isEmpty()) {
            return $this->quantity ?? 0;
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

    // Update stock manually
    public function updateStock(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0'
        ]);

        $product = self::findOrFail($id);
        $product->quantity = $request->quantity;
        $product->save();

        return response()->json(['success' => true, 'quantity' => $product->quantity]);
    }
}
