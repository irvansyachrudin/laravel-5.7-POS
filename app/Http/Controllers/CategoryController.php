<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('created_at', 'DESC')->paginate(10);
        return view('categories.index', compact('categories'));
        // return view('pages.user.index');/
    }

    public function store(Request $request)
    {
        //validasi form
        $this->validate($request, [
            'name' => 'required|string|max:50',
            'description' => 'nullable|string'
        ]);
        try {
            $categories = Category::firstOrCreate([
                'name' => $request->name
            ], [
                'description' => $request->description
            ]);
            return redirect()->back()->with(['success' => 'Kategori: ' . $categories->name . ' Ditambahkan']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $categories = Category::findOrFail($id);
        $categories->delete();
        return redirect()->back()->with(['success' => 'Kategori: ' . $categories->name . ' Telah Dihapus']);
    }

    public function edit($id)
    {
        $categories = Category::findOrFail($id);
        return view('categories.edit', compact('categories'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:50',
            'description' => 'nullable|string'
        ]);
        try {
            //select data berdasarkan id
            $categories = Category::findOrFail($id);
            //update data
            $categories->update([
                'name' => $request->name,
                'description' => $request->description
            ]);

            //redirect ke route kategori.index
            return redirect(route('kategori.index'))->with(['success' => 'Kategori: ' . $categories->name . ' Ditambahkan']);
        } catch (\Exception $e) {
            //jika gagal, redirect ke form yang sama lalu membuat flash message error
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }
    }
}
