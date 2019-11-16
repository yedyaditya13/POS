<?php

namespace App\Http\Controllers;
use App\Category;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(){
        return view('products.index');
    }


    public function create() {
        $categories = Category::orderBy('name', 'ASC')->get();

        return view('products.create', compact($categories));
    }

}
