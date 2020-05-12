<?php

namespace App\Http\Controllers;

use App\Product;
use File;
use Image;
use App\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function index()
    {
        $products = Product::with('category')->orderBy('created_at', 'DESC')->paginate(10);
        return view('products.index', compact('products'));
    }
    public function create()
    {
        $categories = Category::orderBy('name', 'ASC')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|string|max:10|unique:products',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:100',
            'stock' => 'required|integer',
            'price' => 'required|integer',
            'category_id' => 'required|exists:categories,id',
            'photo' => 'nullable|image|mimes:jpg,png,jpeg'
        ]);
        try {
            //default $photo = null
            $photo = null;
            //jika terdapat file (Foto / Gambar) yang dikirim
            if ($request->hasFile('photo')) {
                //maka menjalankan method saveFile()
                $photo = $this->saveFile($request->name, $request->file('photo'));
            }


            //Simpan data ke dalam table products
            $product = Product::create([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description,
                'stock' => $request->stock,
                'price' => $request->price,
                'category_id' => $request->category_id,
                'photo' => $photo
            ]);

            //jika berhasil direct ke produk.index
            return redirect(route('produk.index'))
                ->with(['success' => '<strong>' . $product->name . '</strong> Ditambahkan']);
        } catch (\Exception $e) {
            //jika gagal, kembali ke halaman sebelumnya kemudian tampilkan error
            return redirect()->back()
                ->with(['error' => $e->getMessage()]);
        }
    }

    public function saveFile($name, $photo)
    {
        //set nama file adalah gabungan antara nama produk dan time(). Ekstensi gambar tetap dipertahankan
        $images = str_slug($name) . time() . '.' . $photo->getClientOriginalExtension();
        //set path untuk menyimpan gambar
        $path = public_path('uploads/product');

        //cek jika uploads/product bukan direktori / folder
        if (!File::isDirectory($path)) {
            //maka folder tersebut dibuat
            File::makeDirectory($path, 0777, true, true);
        }
        //simpan gambar yang diuplaod ke folrder uploads/produk
        Image::make($photo)->save($path . '/' . $images);
        //mengembalikan nama file yang ditampung divariable $images
        return $images;
    }

    public function destroy($id)
    {
        //query select berdasarkan id
        $products = Product::findOrFail($id);
        //mengecek, jika field photo tidak null / kosong
        if (!empty($products->photo)) {
            //file akan dihapus dari folder uploads/produk
            File::delete(public_path('uploads/product/' . $products->photo));
        }
        //hapus data dari table
        $products->delete();
        return redirect()->back()->with(['success' => '<strong>' . $products->name . '</strong> Telah Dihapus!']);
    }

    public function edit($id)
    {
        //query select berdasarkan id
        $product = Product::findOrFail($id);
        $categories = Category::orderBy('name', 'ASC')->get();
        return view('products.edit', compact('product', 'categories'));
    }
    public function update(Request $request, $id)
    {
        //validasi
        $this->validate($request, [
            'code' => 'required|string|max:10|exists:products,code',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:100',
            'stock' => 'required|integer',
            'price' => 'required|integer',
            'category_id' => 'required|exists:categories,id',
            'photo' => 'nullable|image|mimes:jpg,png,jpeg'
        ]);

        try {
            //query select berdasarkan id
            $product = Product::findOrFail($id);
            $photo = $product->photo;


            //cek jika ada file yang dikirim dari form
            if ($request->hasFile('photo')) {
                //cek, jika photo tidak kosong maka file yang ada di folder uploads/product akan dihapus
                !empty($photo) ? File::delete(public_path('uploads/product/' . $photo)) : null;
                //uploading file dengan menggunakan method saveFile() yg telah dibuat sebelumnya
                $photo = $this->saveFile($request->name, $request->file('photo'));
            }


            //perbaharui data di database
            $product->update([
                'name' => $request->name,
                'description' => $request->description,
                'stock' => $request->stock,
                'price' => $request->price,
                'category_id' => $request->category_id,
                'photo' => $photo
            ]);

            return redirect(route('produk.index'))
                ->with(['success' => '<strong>' . $product->name . '</strong> Diperbaharui']);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with(['error' => $e->getMessage()]);
        }
    }
}
