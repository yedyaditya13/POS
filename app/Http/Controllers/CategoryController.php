<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index() {
        $categories = Category::orderBy('created_at', 'DESC')->paginate(10);
        return view('categories.index', compact('categories'));
    }


    public function store(Request $request) {
        // validate form
        $this->validate($request, [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string'
        ]);

        try {
            $categories = Category::firstOrCreate([
                'name' => $request->name],
                [
                'description' => $request->description
            ]);
            return redirect()->back()->with(['success' => 'Kategori : ' . $categories->name . 'ditambahkan']);

        } catch (\Throwable $th) {
            return redirect()->back()->with(['error' => $th->getMessage()]);
        }
    }

    public function destroy($id) {
        $categories = Category::findOrFail($id);
        $categories->delete();

        return redirect()->back()->with(['success' => 'Kategori: ' . $categories->name . 'Telah di hapus']);
    }


    public function edit($id){
        $categories = Category::findOrFail($id);

        return view('categories.edit', compact('categories'));
    }


    public function update(Request $request, $id){
        // Validate form
        $this->validate($request, [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string'
        ]);

        try {
            // Select data bedasarkan id
            $categories = Category::findOrFail($id);

            // Update data
            $categories->update([
                'name' => $request->name,
                'description' =>$request->description
            ]);

             //redirect ke route kategori.index
            return redirect(route('kategori.index'))->with(['success' => 'Kategori: ' .$categories->name . 'ditambahkan']);
        } catch (\Throwable $th) {
            //jika gagal, redirect ke form yang sama lalu membuat flash message error
            return redirect()->back()->with(['error' => $th->getMessage()]);
        }
    }
}
