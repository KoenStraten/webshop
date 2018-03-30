<?php

namespace App\Http\Controllers;

use App\Product;
use App\ProductInCart;
use App\ShoppingCart;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShoppingCartController extends Controller
{

    public $shoppingCart;

    public function index()
    {

    }

    public function store()
    {
        $product_id = request('product');
        $amount = request('amount');
        $cheeseType = request('cheeseType');
        $totalCost = 0;

        if (!isset($cheeseType) && !isset($amount)) {
            $cheeseType = 'belegen';
            $amount = 1;
        }

        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->shoppingCarts->where('paid', '0')->last();

            if (!isset($cart)) {
                $shoppingCart = new ShoppingCart();
                $shoppingCart->user_id = $user->id;
                $shoppingCart->total_cost = $totalCost;
                $shoppingCart->save();
            } else {
                $totalCost = $cart->total_cost;
                $shoppingCart = $cart;
            }

            $product = Product::find($product_id);
            $price = $product->price;

            $counter = 0;
            while ($counter < $amount) {
                //$shoppingCart->products()->attach($product);
                $productInCart = new ProductInCart();
                $productInCart->shopping_cart_id = $shoppingCart->id;
                $productInCart->product_id = $product->id;
                $productInCart->cheese_type = $cheeseType;
                $productInCart->save();

                $totalCost = $totalCost + $price;
                $shoppingCart->total_cost = $totalCost;
                $counter++;
            }
            $shoppingCart->save();

            session()->flash('message', 'Het product is toegevoegd aan je winkelmandje.');

            return back();
        } else {
            $cart = new ShoppingCart();
            if (session()->has('cart')) {
                $cart = session()->get('cart');
            }

            $totalCost = 0;

            for ($i = 0; $i < $amount; $i++) {
                $p = Product::find($product_id);
                $pic = new ProductInCart();
                $pic->id = $i;
                $pic->shoppingCart = $cart;
                $pic->product = $p;
                $pic->cheese_type = $cheeseType;
                $totalCost += $p->price;

                session()->push('productsInCart', $pic);
            }

            $cart->total_cost = $cart->total_cost + $totalCost;
            session()->put('cart', $cart);

            session()->flash('message', 'Het product is toegevoegd aan je winkelmandje.');
            return back();
        }
    }

    public function show()
    {
        if (Auth::check()) {
            $user = Auth::user();

            $cart = $user->shoppingCarts->where('paid', 0)->last();

            if (isset($cart)) {
                $productsInCart = ProductInCart::where('shopping_cart_id', $cart->id)->get();
            } else {
                $this->newCart();
            }
        } else {
            $productsInCart = session('productsInCart');
            if (isset($productsInCart)) {
                $productsInCart = collect($productsInCart);
            }
        }

        return view('pages/shoppingcart', compact('productsInCart'));
    }

    public function newCart()
    {
        $user = Auth::user();

        $shoppingCart = new ShoppingCart();
        $shoppingCart->user_id = $user->id;
        $totalCost = 0;
        $shoppingCart->total_cost = $totalCost;
        $shoppingCart->save();

        return $shoppingCart;
    }

    public function remove()
    {
        $productInCart_id = request('productInCart');
        if (Auth::check()) {
            $productInCart = ProductInCart::find($productInCart_id);
            $product = Product::find($productInCart->product_id);

            $user = Auth::user();

            $cart = $user->shoppingCarts->where('paid', 0)->last();

            $totalCost = $cart->total_cost;

            $cart->total_cost = $totalCost - $product->price;

            $cart->save();

            ProductInCart::find($productInCart->id)->delete();
        } else {
            $productsInCart = session()->get('productsInCart');
            $cart = session()->get('cart');
            unset($productsInCart[$productInCart_id]);
            session()->forget('productsInCart');
            $totalCost = 0;
            foreach($productsInCart as $p) {
                $totalCost = $totalCost + $p->product->price;
                session()->push('productsInCart', $p);
            }
            $cart->total_cost = $totalCost;
            session()->forget('cart');
            session()->put('cart', $cart);
        }

        session()->flash('message', 'Het product is verwijderd van je winkelmandje.');

        return redirect('/shoppingcart/');
    }

    public function removeAll() {
        if (Auth::check()) {
            $shoppingCartId = request('shopping_cart_id');

            $productsInCart = ProductInCart::where('shopping_cart_id', $shoppingCartId)->get();

            foreach($productsInCart as $p) {
                $p->delete();
            }

            $shoppingCart = ShoppingCart::find($shoppingCartId);
            $shoppingCart->total_cost = 0;
            $shoppingCart->save();
        } else {
            session()->forget('productsInCart');
            $cart = session()->get('cart');
            $cart->total_cost = 0;
            session()->forget('cart');
            session()->put('cart', $cart);
        }

        session()->flash('message', 'De producten zijn verwijderd van je winkelmandje.');

        return redirect('/shoppingcart/');
    }

    public function purchase()
    {
        $user = Auth::user();
        if (isset($user)) {
            $cart = $user->shoppingCarts->where('paid', '0')->last();
        }

        return view('pages.purchase', compact('cart', 'user'));
    }

    public function emptyCart()
    {
        $cart_id = request('cart_id');

        $cart = ShoppingCart::find($cart_id);

        $cart->paid = 1;

        $cart->save();

        return redirect('/');
    }
}