<?php

namespace App\Http\Controllers\Admins;
use App\Models\Product\RawMaterial;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product\Product;
use App\Models\Product\Variant;

class VariantController extends Controller
{
    // ========================
    // Show all variants for a product
    // ========================
    public function index($productId)
    {
        $product = Product::with('variants')->findOrFail($productId);
        return view('admins.variant.index', compact('product'));
    }

    // ========================
    // Show form to create a new variant
    // ========================
    public function create($productId)
    {
        $product = Product::findOrFail($productId);
        return view('admins.variant.create', compact('product'));
    }

    // ========================
    // Store new variant
    // ========================
    public function store(Request $request, $productId)
    {
        $request->validate([
            'name' => 'required|max:100',
            'price' => 'required|numeric',
        ]);

        $product = Product::findOrFail($productId);

        $variant = $product->variants()->create([
            'name' => $request->name,
            'price' => $request->price,
        ]);

        // Check if AJAX request
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'variant' => $variant,
            ]);
        }

        // Fallback: normal redirect
        return back()->with('success', 'Variant created successfully.');
    }


    // ========================
    // Show form to edit a variant
    // ========================
    public function edit($id)
    {
        $variant = Variant::with('rawMaterials')->findOrFail($id);
        $rawMaterials = RawMaterial::all();
        return view('admins.variant.edit', compact('variant', 'rawMaterials'));
    }

    // ========================
    // Update variant info
    // ========================
    public function update(Request $request, $id)
    {
        $variant = Variant::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $variant->update([
            'name'  => $request->name,
            'price' => $request->price,
        ]);

        return redirect()->route('admins.variants.index', $variant->product_id)
                         ->with('success', 'Variant updated successfully.');
    }

    // ========================
    // Delete variant
    // ========================
    public function destroy($variantId)
    {
        $variant = Variant::findOrFail($variantId);
        $productId = $variant->product_id;
        $variant->delete();

        return redirect()->route('admin.product.variants.create', $productId)
                        ->with('delete', 'Variant deleted successfully.');
    }


    // ========================
    // Show form to assign raw materials to a variant
    // ========================
    public function assignMaterials($id)
    {
        $variant = Variant::with('rawMaterials')->findOrFail($id);
        $rawMaterials = RawMaterial::all();
        return view('admins.variant.assign_materials', compact('variant', 'rawMaterials'));
    }

    // ========================
    // Store / update raw materials for a variant
    // ========================
    public function storeMaterials(Request $request, $id)
    {
        $variant = Variant::findOrFail($id);

        $materials = $request->input('materials', []);
        $syncData = [];
        foreach ($materials as $materialId => $quantity) {
            if ($quantity > 0) {
                $syncData[$materialId] = ['quantity_required' => $quantity];
            }
        }

        $variant->rawMaterials()->sync($syncData);

        // Get product ID from the variant
        $productId = $variant->product_id;

        return redirect()->route('admin.product.variants.create', $productId)
                        ->with('success', 'Raw materials assigned successfully.');



    }
}
