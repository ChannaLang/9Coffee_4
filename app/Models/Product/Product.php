<?php
namespace App\Models\Product;
use App\Models\Product\ProductType; // <-- import the correct class
use App\Models\Product\SubType;  
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


    // Orders relationship
    public function orders()
    {
        return $this->hasMany(\App\Models\Product\Order::class);
    }
    // NEW: Product has many variants
    public function variants()
    {
        return $this->hasMany(Variant::class);
    }

    // Check if we should use variant pricing
    public function hasVariants()
    {
        return $this->variants()->exists();
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
