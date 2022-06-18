<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $perPage = request()->get('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $products = Product::query()->search();

        return response()->json([
            $products->paginate($perPage)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductRequest $request
     * @param Shop $shop
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request, Shop $shop): JsonResponse
    {
        $product = $shop->products()->create($request->validated());

        return response()->json([
            'status' => 200,
            'message' => 'Product created.',
            'data' => $product
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param Shop $shop
     * @param Product $product
     * @return JsonResponse
     */
    public function show(Shop $shop, Product $product): JsonResponse
    {
        $product->shop = $shop;

        return response()->json([
            'status' => 200,
            'data' => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        if (auth()->id() === Shop::find($product->shop_id)->user_id) {
            $product->update($request->validated());

            return response()->json([
                'status' => 200,
                'message' => 'Product updated.'
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'This is not your product!'
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        if (auth()->id() === Shop::find($product->shop_id)->user_id) {
            $product->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Product deleted.'
            ]);
        } else {
            return response()->json([
                'This is not your product!'
            ]);
        }
    }
}
