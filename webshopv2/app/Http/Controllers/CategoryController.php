<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use App\Product;

class CategoryController extends Controller
{
    public function show($category) {
        $products = Product::getAllProductsByCategory($category);

        return view('pages.category', compact('products', 'category'));
    }
}
