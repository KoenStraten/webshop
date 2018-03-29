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
                $totalCost = 0;
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
            session()->push('shoppingcart.products', $product_id);
            session()->push('product.cheesetypes', $cheeseType);
            session()->push('shoppingcart.totalcost', $totalCost);
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
            $cart = new ShoppingCart();
            $cart->total_cost = 15;
            $productIds = session('shoppingcart.products');
            $cheeseTypes = session('shoppingcart.cheesetypes');
            if (isset($productIds)) {
                $productsInCart = array();
                for($i = 0; $i < count($productIds); $i++) {
                    $pic = new ProductInCart();
                    $pic->product = Product::find($productIds[$i]);
                    $pic->shoppingCart = $cart;
                    $pic->cheese_type = $cheeseTypes[$i];
                    array_push($productsInCart, $pic);
                }
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
        $productInCart = ProductInCart::find($productInCart_id);
        $product = Product::find($productInCart->product_id);

        $user = Auth::user();

        $cart = $user->shoppingCarts->where('paid', 0)->last();

        $totalCost = $cart->total_cost;

        $cart->total_cost = $totalCost - $product->price;

        $cart->save();

        ProductInCart::find($productInCart->id)->delete();

        session()->flash('message', 'Het product is verwijderd van je winkelmandje.');

        return redirect('/shoppingcart/');
    }

    public function removeAll() {
        $shoppingCartId = request('shopping_cart_id');

        $productsInCart = ProductInCart::where('shopping_cart_id', $shoppingCartId)->get();

        foreach($productsInCart as $p) {
            $p->delete();
        }

        $shoppingCart = ShoppingCart::find($shoppingCartId);
        $shoppingCart->total_cost = 0;
        $shoppingCart->save();

        session()->flash('message', 'De producten zijn verwijderd van je winkelmandje.');

        return redirect('/shoppingcart/');
    }

    public function purchase()
    {
        $cart_id = request('cart_id');

        $cart = ShoppingCart::find($cart_id);

        $user = Auth::user();

//        $productsInCart = ProductInCart::where('shopping_cart_id', $cart_id)->get();

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