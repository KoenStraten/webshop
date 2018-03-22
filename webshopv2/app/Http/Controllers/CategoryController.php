<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use App\Product;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin')->except('index', 'show');
    }

    public function index()
    {
        $categories = Category::all();

        return view('pages.categoryoverview', compact( 'categories'));
    }

    public function show($category)
    {
        $products = Product::getAllProductsByCategory($category);

        return view('pages.category', compact('products', 'category'));
    }

    public function categoryIndex() {
        $categories = Category::all();

        return view('pages.admin.categories.index', compact('categories'));
    }

    public function create() {
        return view('pages/admin/categories/create');
    }

    public function edit($category) {
        $category = Category::find($category);
        return view('pages/admin/categories/edit', compact('category'));
    }

    public function update() {
        $this->validate(request(), [
            'category' => 'required|min:2',
        ]);
        
        $category = Category::find(request('category_old'));
        $category->category = request('category');
        $category->save();

        return redirect('/admin/categories');
    }

    public function store() {
        $this->validate(request(), [
            'category' => 'required|min:2',
        ]);

        $category = new Category();
        $category->category = request('category');
        $category->save();

        return redirect('/../admin/categories');
    }
    public function remove($category) {
        Category::find($category)->delete();
        return redirect('/../admin/categories');
    }
}
