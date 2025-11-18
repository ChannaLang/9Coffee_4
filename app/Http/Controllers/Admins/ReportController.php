<?php
namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product\Product;
use App\Exports\SalesReportExport;
use App\Models\Product\Order;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
public function viewStock()
{
    $products = Product::all();
    return view('admins.stock', compact('products'));
}


public function updateStock(Request $request, $id)
{
    $request->validate([
        'quantity' => 'required|integer|min:0',
    ]);
    $product = Product::findOrFail($id);
    $product->quantity = $request->quantity;
    $product->save();
    return redirect()->back()->with('success', 'Stock updated successfully!');
}


public function salesReport()
{
    $sales = DB::table('orders')
        ->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(quantity) as total_orders'), // sum quantities, not count rows
            DB::raw('SUM(price) as total_sales')
        )
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date', 'desc')
        ->get();

    return view('admins.sales', compact('sales'));
}


public function addQuantity(Request $request, $id)
{
    $request->validate([
        'quantity' => 'required|integer|min:1',
    ]);

    $product = Product::with('rawMaterials')->findOrFail($id);

    // Update raw materials
    foreach ($product->rawMaterials as $material) {
        $material->quantity += $request->quantity * $material->pivot->quantity_required;
        $material->save();
    }

    // Recalculate available stock
    $minStock = null;
    foreach ($product->rawMaterials as $material) {
        if ($material->pivot->quantity_required > 0) {
            $stockForThisMaterial = floor($material->quantity / $material->pivot->quantity_required);
            $minStock = is_null($minStock) ? $stockForThisMaterial : min($minStock, $stockForThisMaterial);
        }
    }
    $product->available_stock = $minStock ?? 0;

    return response()->json([
        'success' => true,
        'new_quantity' => $product->available_stock,
        'message' => $product->name . ' stock added!',
    ]);
}



}
