<?php

namespace App\Http\Controllers\Admins;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Product\RawMaterial;


class RawMaterialController extends Controller
{
    public function viewRawMaterials()
    {
        $rawMaterials = RawMaterial::orderBy('id', 'asc')->get();
        return view('admins.stock', compact('rawMaterials'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|unique:raw_materials,id',
            'name' => 'required|string|max:100|unique:raw_materials,name',
            'unit' => 'required|string|max:10',
            'quantity' => 'required|integer|min:0',
        ]);

        $material = RawMaterial::create($request->only('id','name','unit','quantity'));

        return response()->json($material);
    }

    public function updateMaterial(Request $request, $id)
    {
        $material = RawMaterial::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:100|unique:raw_materials,name,'.$id,
            'unit' => 'required|string|max:10',
        ]);
        if ($request->has('new_id')) $material->id = $request->new_id;
        $material->name = $request->name;
        $material->unit = $request->unit;
        $material->save();
        return response()->json($material);
    }

public function addQuantity(Request $request, $id)
{
    $request->validate([
        'quantity' => 'required|numeric|min:0.01', // allow decimals
    ]);

    $material = RawMaterial::findOrFail($id);
    $material->quantity += floatval($request->quantity); // make sure to add float
    $material->save();

    return response()->json($material);
}

public function reduceQuantity(Request $request, $id)
{
    $request->validate([
        'quantity' => 'required|numeric|min:0.01', // allow decimals
    ]);

    $material = RawMaterial::findOrFail($id);

    $reduceQty = floatval($request->quantity);

    if ($material->quantity < $reduceQty) {
        return response()->json(['message' => 'Not enough stock!'], 400);
    }

    $material->quantity -= $reduceQty;
    $material->save();

    return response()->json($material);
}

    public function deleteRawMaterial($id)
    {
        $material = RawMaterial::findOrFail($id);

        if ($material->quantity > 0) {
            return response()->json(['message' => 'Cannot delete material with stock'], 400);
        }

        $material->delete();
        return response()->json(['success' => true]);
    }

    public function listMaterials(): JsonResponse
    {
        try {
            $materials = RawMaterial::select('id', 'name', 'quantity', 'unit')->get();
            return response()->json($materials);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch materials',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
