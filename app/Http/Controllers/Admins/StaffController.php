<?php

namespace App\Http\Controllers\Admins;

use App\Models\Product\Product;
use App\Models\ProductType;
use App\Models\SubType;
use App\Models\Product\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class StaffController extends Controller
{
    public function StaffSellForm()
    {
        $products = Product::with('type', 'subType', 'rawMaterials')->orderBy('id', 'asc')->get();
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
                $product = Product::with('rawMaterials')->find($item['id']);
                if (!$product) continue;

                if ($item['quantity'] > $product->quantity) {
                    throw new \Exception("Not enough stock for {$product->name}");
                }

                // Deduct product stock
                $product->quantity = $product->quantity - $item['quantity'];
                if ($product->quantity < 0) {
                    throw new \Exception("Not enough stock for {$product->name}");
                }
                $product->save();

                // Deduct raw materials
                foreach ($product->rawMaterials as $material) {
                    // Get pivot entry for the selected size
                    $pivot = $product->rawMaterials()
                        ->where('raw_material_id', $material->id)
                        ->where('size', $item['size'] ?? 'S')
                        ->first();

                    if (!$pivot) continue;

                    $requiredQty = $pivot->pivot->quantity_required * $item['quantity'];

                    if ($material->quantity < $requiredQty) {
                        throw new \Exception("Not enough {$material->name} for {$product->name} ({$item['size']})");
                    }

                    $material->quantity -= $requiredQty;
                    $material->save();
                }


                // Create order
                $lineTotal = $item['unit_price'] * $item['quantity'];
                $product->orders()->create([
                    'user_id'        => Auth::id(),
                    'product_id'     => $product->id,
                    'quantity'       => $item['quantity'],
                    'size'           => $item['size'] ?? 'S',
                    'sugar'          => $item['sugar'] ?? '50',
                    'price'          => $lineTotal,
                    'status'         => 'Paid Successfully',
                    'payment_status' => 'Paid',
                    'payment_method' => $paymentMethod,
                    'first_name'     => 'Walk-in',
                    'last_name'      => 'Customer',
                ]);

                $updatedStock[$product->id] = $product->quantity;
                $totalAmount += $lineTotal;
            }

            // Update staff balance
            // $user = Auth::user();
            // $user->balance = ($user->balance ?? 0) + $totalAmount;
            // $user->save();

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
