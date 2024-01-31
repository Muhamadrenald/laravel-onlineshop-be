<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    //index
    public function index()
    {
        $products = \App\Models\Product::paginate(5);
        return view('pages.product.index', compact('products'));
    }

    //create
    public function create()
    {
        $categories = \App\Models\Category::all();
        return view('pages.product.create', compact('categories'));
    }

    //store
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3|unique:products',
            // 'description' => 'required|min:10',
            'price' => 'required|integer',
            'stock' => 'required|integer',
            'category_id' => 'required',
            'image' => 'required|image|mimes:png,jpg,jpeg,webp'
        ]);

        $filename = time() . '.' . $request->image->extension();
        $request->image->storeAs('public/products', $filename);
        $data = $request->all();

        $product = new \App\Models\Product;
        $product->name = $request->name;
        $product->price = (int) $request->price;
        $product->stock = (int) $request->stock;
        $product->category_id = $request->category_id;
        $product->image = $filename;
        $product->save();

        return redirect()->route('product.index')->with('success', 'Product successfully created');
    }

    //edit
    public function edit($id)
    {
        $product = \App\Models\Product::findOrFail($id);
        $categories = \App\Models\Category::all();
        return view('pages.product.edit', compact('product', 'categories'));
    }

    //update
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|min:3|unique:products,name,' . $id,
            'price' => 'required|integer',
            'stock' => 'required|integer',
            'category_id' => 'required',
            'image' => 'image|mimes:png,jpg,jpeg,webp'
        ]);

        $imagePath = Product::find($id)->image;

        if ($request->hasFile('image')) {
            if (Storage::disk('public')->exists('products/' . $imagePath)) {
                Storage::disk('public')->delete('products/' . $imagePath);
            }
            $imagePath = time() . '.' . $request->image->extension();
            $request->image->storeAs('public/products', $imagePath);
        }

        $data = $request->all();
        $product = Product::findOrFail($id);
        $data['image'] = $imagePath;
        $product->update($data);
        return redirect()->route('product.index')->with('success', 'Product successfully updated');
    }

    //destroy
    public function destroy($id)
    {
        $product = \App\Models\Product::findOrFail($id);

        // Hapus gambar dari direktori
        if (Storage::disk('public')->exists('products/' . $product->image)) {
            Storage::disk('public')->delete('products/' . $product->image);
        }

        // Hapus entitas produk dari database
        $product->delete();

        return redirect()->route('product.index')->with('success', 'Product successfully deleted');
    }
}
