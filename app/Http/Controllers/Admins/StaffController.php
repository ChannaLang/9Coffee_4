<?php

namespace App\Http\Controllers\Admins;
use App\Models\Product\Variant;
use App\Models\Product\Product;
use App\Models\Product\ProductType;
use App\Models\Product\SubType;
use App\Models\Product\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class StaffController extends Controller
{
    public function StaffSellForm()
    {
        $products = Product::with(['type', 'subType', 'variants.rawMaterials'])
                    ->orderBy('id', 'asc')
                    ->get();

        $productsType = $products->groupBy(fn($p) => strtolower($p->type->name ?? 'others'));
        $types = ProductType::all();
        $subTypes = SubType::all();
        $earning = Order::sum('price');

        return view('admins.staffSell', compact('products', 'productsType', 'types', 'subTypes', 'earning'));
    }

    public function staffCheckout(Request $request)
    {
        $cart = json_decode($request->cart_data, true);
        $paymentMethod = $request->payment_method ?? 'Cash';

        if (empty($cart) || !is_array($cart)) {
            return response()->json(['success' => false, 'message' => 'Cart is empty or invalid!']);
        }

        $updatedStock = [];
        $totalAmount = 0;

        DB::beginTransaction();

        try {
            foreach ($cart as $item) {

                // Load VARIANT not product
                $variant = Variant::with(['rawMaterials', 'product'])->find($item['id']);
                if (!$variant) continue;

                $product = $variant->product; // parent product

                // Check & deduct variant stock
                if ($item['quantity'] > $variant->quantity) {
                    throw new \Exception("Not enough stock for variant {$variant->name}");
                }

                $variant->quantity -= $item['quantity'];
                $variant->save();

                // Deduct raw materials
                foreach ($variant->rawMaterials as $material) {

                    $requiredQty = $material->pivot->quantity_required * $item['quantity'];

                    if ($material->quantity < $requiredQty) {
                        throw new \Exception(
                            "Not enough {$material->name} for {$product->name} ({$variant->name})"
                        );
                    }

                    $material->quantity -= $requiredQty;
                    $material->save();
                }

                // Create order
                $lineTotal = $item['unit_price'] * $item['quantity'];

                Order::create([
                    'user_id'        => Auth::id(),
                    'product_id'     => $product->id,
                    'variant_id'     => $variant->id,
                    'quantity'       => $item['quantity'],
                    'size'           => $variant->name,
                    'sugar'          => $item['sugar'] ?? '50',
                    'price'          => $lineTotal,
                    'status'         => 'Paid Successfully',
                    'payment_status' => 'Paid',
                    'payment_method' => $paymentMethod,
                    'first_name'     => 'Walk-in',
                    'last_name'      => 'Customer',
                ]);

                $updatedStock[$variant->id] = $variant->quantity;
                $totalAmount += $lineTotal;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Checkout successful!',
                'updated_stock' => $updatedStock,
                'total_amount' => $totalAmount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

}
