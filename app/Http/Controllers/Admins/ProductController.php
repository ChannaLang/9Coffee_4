<?php
namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product\Product;
use Illuminate\Support\Facades\File;
use App\Models\RawMaterial;

class ProductController extends Controller
{
    public function DisplayProducts(){
        $products = Product::select()->orderBy('id','asc')->get();
            return view('admins.allproducts',compact('products'));

    }
    public function StoreProducts(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:products,name|max:100',
            'price' => 'required|numeric',
            'product_type_id' => 'required|exists:product_types,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif',
            'description' => 'nullable',
        ]);

        $imagePath = public_path('assets/images');
        if (!file_exists($imagePath)) {
            mkdir($imagePath, 0775, true);
        }

        $imageName = time() . '_' . $request->image->getClientOriginalName();
        $request->image->move($imagePath, $imageName);

        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'image' => $imageName,
            'description' => $request->description,
            'product_type_id' => $request->product_type_id,
            'quantity' => $request->quantity ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully!',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image' => $product->image,
                'product_type_name' => $product->productType->name ?? 'N/A'
            ]
        ]);
    }


    public function DeleteProducts($id){
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found']);
        }

        if (File::exists(public_path('assets/images/' . $product->image))) {
            File::delete(public_path('assets/images/' . $product->image));
        }

        $product->delete();

        return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
}


         public function EditProducts($id)
    {
        $product = Product::findOrFail($id);
        return view('admins.edit', compact('product'));
    }

    public function AjaxUpdateProducts(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found']);
        }

        $request->validate([
            'name' => 'required|max:100',
            'price' => 'required|numeric',
            'product_type_id' => 'required|exists:product_types,id'
        ]);

        $product->name = $request->name;
        $product->price = $request->price;
        $product->product_type_id = $request->product_type_id;

        $product->save();

        return response()->json(['success' => true, 'message' => 'Product updated successfully']);
    }

        // Fetch all raw materials with assigned quantity for a product
        public function getMaterials($id)
        {
            $product = Product::with('rawMaterials')->findOrFail($id);

            $rawMaterials = RawMaterial::all()->map(function ($mat) use ($product) {
                $assigned = $product->rawMaterials->firstWhere('id', $mat->id);
                return [
                    'id' => $mat->id,
                    'name' => $mat->name,
                    'unit' => $mat->unit,
                    'quantity' => $mat->quantity,
                    'assigned_qty' => $assigned ? $assigned->pivot->quantity_required : 0,
                ];
            });

            return response()->json($rawMaterials);
        }

        public function getAssignedMaterials($id)
        {
            $product = Product::with('rawMaterials')->findOrFail($id);

            $assigned = $product->rawMaterials->groupBy('pivot.size')->map(function($group, $size) {
                return $group->map(function($mat) use ($size) {
                    return [
                        'id' => $mat->id,
                        'name' => $mat->name,
                        'unit' => $mat->unit,
                        'size' => $size,
                        'quantity_required' => $mat->pivot->quantity_required ?? 0,
                    ];
                });
            });

            return response()->json($assigned);
        }



                // Assign / update raw materials for a product
        public function addMaterials(Request $request, $id)
        {
            $product = Product::findOrFail($id);

            $data = $request->validate([
                'materials' => 'required|array',
                'materials.*' => 'array',          // each raw material is an array of sizes
                'materials.*.*' => 'numeric|min:0' // each size has numeric qty
            ]);

            $syncData = [];

            foreach ($data['materials'] as $rawId => $sizes) {
                foreach ($sizes as $size => $qty) {
                    if ($qty > 0) {
                        $syncData[] = [
                            'raw_material_id' => $rawId,
                            'quantity_required' => $qty,
                            'size' => $size
                        ];
                    }
                }
            }

            // First detach all existing assignments for this product
            $product->rawMaterials()->detach();

            // Attach new assignments with size
            foreach ($syncData as $item) {
                $product->rawMaterials()->attach($item['raw_material_id'], [
                    'quantity_required' => $item['quantity_required'],
                    'size' => $item['size']
                ]);
            }

            return redirect()->route('admin.product.assignPage', $product->id)
                        ->with('success', 'Recipe updated successfully!');

        }


        public function showAssignPage($id)
        {
            $product = Product::with('rawMaterials')->findOrFail($id);
            $rawMaterials = RawMaterial::all();
            $sizes = ['S', 'M', 'L'];

            // Prepare assigned quantities
            $assigned = [];
            foreach ($product->rawMaterials as $mat) {
                $assigned[$mat->id][$mat->pivot->size] = $mat->pivot->quantity_required;
            }

            return view('admins.assign_recipe', compact('product', 'rawMaterials', 'sizes', 'assigned'));
        }
        public function editAssigned(Request $request, $id)
        {
            $product = Product::findOrFail($id);
            $matId = $request->matId;
            $quantities = $request->input('quantities');

            foreach ($quantities as $size => $qty) {
                $product->rawMaterials()->updateExistingPivot($matId, [
                    'quantity_required' => $qty,
                    'size' => $size
                ]);
            }

            return response()->json([
                'success' => true,
                'quantities' => $quantities
            ]);
        }










}
