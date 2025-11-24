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
        $request->validate([
            'name' => 'required|string|unique:product_types,name'
        ]);

        $type = ProductType::create(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'type' => $type
        ]);
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
