<?php

namespace App\Http\Controllers;
use App\Category;
use App\Product;
use File;
use Image;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(){
        $products = Product::with('category')->orderBy('created_at', 'DESC')->paginate(10);
        return view('products.index', compact('products'));
    }


    public function create() {
        $categories = Category::orderBy('name', 'ASC')->get();

        return view('products.create', compact('categories'));
    }

    public function store(Request $request) {
        $this->validate($request, [
            'code' => 'required|string|unique:products',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:100',
            'stock' => 'required|integer',
            'price' => 'required|integer',
            'category_id' => 'required|exists:categories,id',
            'photo' => 'nullable|image|mimes:jpg,png,jpeg'

        ]);

        try {
            // defaut $photo = null
            $photo = null;

            // jika terdapat file (Foto / gambar) yang dikirim
            if ($request->hasFile('photo')) {
                // Maka menjalankan method saveFile()
                $photo = $this->saveFile($request->name, $request->file('photo'));
            }


            // Simpan data ke dalam table products
            $product = Product::create([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description,
                'stock' => $request->stock,
                'price' => $request->price,
                'category_id' => $request->category_id,
                'photo' => $photo
            ]);

            // Jika berhasil direct ke produk.index
            return redirect(route('produk.index'))
                ->view(['success' => '<strong>' . $product->name . '</strong> ditambahkan']);

        } catch (\Throwable $th) {
            return redirect()->back()->with(['error' => $th->getMessage()]);
        }
    }


    public function saveFile($name, $photo) {
        //set nama file adalah gabungan antara nama produk dan time().
        // Ekstensi gambar tetap dipertahankan
        $images = str_slug($name) . time() . '.' . $photo->getClientOriginalExtension();

        // set path untuk menyimpan gambar
        $path = public_path('uploads/product');

        // cek jika uploads/product bukan direktori / folder
        if (!FileStorage::isDirectory($path)) {
            // Maka folder tersebut akan dibuat
            FileStorage::makeDirectory($path, 0777, true, true);
        }

        // simpan gambar yang di uload ke folder uploads/produk
        Image::make($photo)->save($path . '/' .$images);

        // mengembalikan nama file yang di tambung di variable $images
        return $images;
    }

}
