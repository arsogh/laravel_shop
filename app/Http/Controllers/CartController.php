<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): JsonResponse
    {
        $cartItems = Cart::where('user_id', auth()->id())->with('product')->get();

        return response()->json([
            'message' => 'Carts selected.',
            'cartData' => $cartItems
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse
    {
        $save = new Cart;
        $save->user_id = auth()->id();
        $save->product_id = $request->product_id;
        $save->count = $request->count;
        $save->save();

        return response()->json([
            'message' => 'Cart created.'
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id): JsonResponse
    {
        Cart::findOrFail($id)->update($request->all());

        return response()->json([
            'message' => 'Cart updated.'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id): JsonResponse
    {
        Cart::find($id)->delete();

        return response()->json([
            'message' => 'Cart deleted.'
        ], 200);
    }

    public function checkout(): JsonResponse
    {
        $cartItems = Cart::where('user_id', auth()->id())->with('product')->get();
        if (!count($cartItems)) {
            return response()->json([
                'status' => 200,
                'message' => 'You have not any products on your cart.'
            ]);
        }

        $failed = $cartItems->map(function ($item) {
            if ($item->product->count < $item->count) {
                $prodCount = $item->product->count;
                $prodId = $item->product->id;
                return "Product by id=$prodId available count for product item $prodCount, but you want to buy $item->count.";
            }
        });

        if ($failed[0] !== null) {
            return response()->json([
                'status' => 200,
                'failed' => $failed
            ]);
        }

        $subtotal = $cartItems->map(function ($item) {
            return $item->product->price * $item->count;
        })->sum();

        try {
            DB::beginTransaction();

            $products = $cartItems->map(function ($item) {
                $item->product->update([
                    'count' => $item->product->count - $item->count
                ]);
                $item->delete();
                return [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'count' => $item->count,
                    'price' => $item->product->price
                ];
            });

            Order::create([
                'user_id' => auth()->id(),
                'products' => $products,
                'subtotal' => $subtotal
            ]);

            DB::commit();
            return response()->json([
                'status' => 200,
                'CheckOut' => 'Thank you for shopping'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 400,
                'message' => 'Something wrong.'
            ]);
        }
    }
}