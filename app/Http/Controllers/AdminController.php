<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // READ
    public function index()
    {
        $products = Product::with('category')->latest()->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    // CREATE
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    // STORE
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required',
            'category_id' => 'required',
            'brand'       => 'required',
            'price'       => 'required|numeric',
            'size'        => 'required',
            'condition'   => 'required',
            'stock'       => 'required|integer',
            'description' => 'required',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        Product::create([
            'title'       => $request->title,
            'category_id' => $request->category_id,
            'brand'       => $request->brand,
            'price'       => $request->price,
            'size'        => $request->size,
            'condition'   => $request->condition,
            'stock'       => $request->stock,
            'description' => $request->description,
            'image'       => $imagePath,
            'status'      => $request->stock > 0 ? 'available' : 'sold_out',
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan!');
    }

    // EDIT
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'title'       => 'required',
            'category_id' => 'required',
            'brand'       => 'required',
            'price'       => 'required|numeric',
            'size'        => 'required',
            'condition'   => 'required',
            'stock'       => 'required|integer',
            'status'      => 'required',
            'description' => 'required',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // upload gambar baru, hapus gambar lama
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->image = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'title'       => $request->title,
            'category_id' => $request->category_id,
            'brand'       => $request->brand,
            'price'       => $request->price,
            'size'        => $request->size,
            'condition'   => $request->condition,
            'stock'       => $request->stock,
            'status'      => $request->stock == 0 ? 'sold_out' : $request->status,
            'description' => $request->description,
            'image'       => $product->image,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diupdate!');
    }

    // DELETE
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus!');
    }

    // Cetak laporan 
    public function exportPdf()
    {
        $products = Product::with('category')->get();
        $orders = Order::with('user')->latest()->get();

        $pdf = Pdf::loadView('pdf.report', compact('products', 'orders'));
        return $pdf->download('laporan-stok-helm.pdf');
    }
}
