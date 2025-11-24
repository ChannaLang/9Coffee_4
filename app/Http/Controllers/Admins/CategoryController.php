<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product\ProductType;
use App\Models\Product\SubType;

class CategoryController extends Controller
{
    // Show all types and subtypes
    public function index()
    {
        $types = ProductType::with('subTypes')->get(); // eager load subtypes
        return view('admins.category', compact('types'));

    }

    // Store new product type
    public function storeType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:product_types,name'
        ]);

        $type = ProductType::create(['name' => $validated['name']]);

        return response()->json([
            'success' => true,
            'type' => $type
        ]);
    }
    // Delete a single type
    public function deleteType($id)
    {
        $type = ProductType::find($id);

        if(!$type) {
            return response()->json(['success' => false, 'message' => 'Type not found']);
        }

        $type->delete();

        return response()->json(['success' => true]);
    }

    // Delete multiple types
    public function deleteMultipleTypes(Request $request)
    {
        $request->validate([
            'type_ids' => 'required|array',
            'type_ids.*' => 'exists:product_types,id'
        ]);

        ProductType::whereIn('id', $request->type_ids)->delete();

        return response()->json(['success' => true]);
    }




    // Store new product subtype
    public function storeSubtype(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'product_type_id' => 'required|exists:product_types,id'
        ]);

        $subtype = SubType::create([
            'name' => $request->name,
            'product_type_id' => $request->product_type_id
        ]);

        return response()->json([
            'success' => true,
            'subtype' => $subtype
        ]);
    }
    public function deleteSubtype($id)
    {
        $subtype = \App\Models\Product\SubType::find($id);

        if (!$subtype) {
            return redirect()->back()->with('error', 'Subtype not found.');
        }

        $subtype->delete();

        return redirect()->back()->with('success', 'Subtype deleted successfully.');
    }

}
