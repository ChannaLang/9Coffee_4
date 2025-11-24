<?php
namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Correct import
use App\Models\Product\Product;
use Illuminate\Support\Facades\File;


class ProductController extends Controller
{
    public function DisplayProducts(){
        $products = Product::with(['productType', 'subType'])->orderBy('id','asc')->get();
            return view('admins.allproducts',compact('products'));

    }
public function StoreProducts(Request $request)
{
    $request->validate([
        'name' => 'required|unique:products,name|max:100',
        'price' => 'required|numeric',
        'product_type_id' => 'required|exists:product_types,id',
        'sub_type_id' => 'required|exists:sub_types,id',
        'image' => 'required|image|mimes:jpeg,png,jpg,gif',
        'description' => 'nullable',
    ]);

    $imagePath = public_path('assets/images');
    if (!file_exists($imagePath)) mkdir($imagePath, 0775, true);

    $imageName = time() . '_' . $request->image->getClientOriginalName();
    $request->image->move($imagePath, $imageName);

    $product = Product::create([
        'name' => $request->name,
        'price' => $request->price,
        'image' => $imageName,
        'description' => $request->description,
        'product_type_id' => $request->product_type_id,
        'sub_type_id' => $request->sub_type_id,
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
            'product_type_name' => $product->productType->name ?? 'N/A',
            'sub_type_name' => $product->subType->name ?? 'N/A'
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
            'product_type_id' => 'required|exists:product_types,id',
            'sub_type_id' => 'required|exists:sub_types,id',
        ]);

        // Update all fields
        $product->name = $request->name;
        $product->price = $request->price;
        $product->product_type_id = $request->product_type_id;
        $product->sub_type_id = $request->sub_type_id;

        $product->save();

        return response()->json(['success' => true, 'message' => 'Product updated successfully']);
    }



}
