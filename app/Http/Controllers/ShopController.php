<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShopRequest;
use App\Http\Requests\UpdateShopRequest;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        /** @var User $user */
        $perPage = request()->get('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $user = auth()->user();
        $shops = Shop::query()
            ->when($user->isSeller(), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });

        return response()->json([
            'status' => trans('shop.success'),
            'data' => $shops->paginate($perPage)
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreShopRequest $request
     * @return JsonResponse
     */
    public function store(StoreShopRequest $request): JsonResponse
    {
        $shop = Shop::create($request->validated());
        return response()->json([
            'data' => $shop
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param Shop $shop
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $shop = Shop::query()
            ->when($user->isSeller(), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($id);
        return response()->json([
            'data' => $shop
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateShopRequest $request
     * @param Shop $shop
     * @return JsonResponse
     */
    public function update(UpdateShopRequest $request, Shop $shop): JsonResponse
    {
        $shop->update($request->validated());
        return response()->json([
            'status' => 200,
            'message' => 'Shop updated.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $shop = Shop::forUser()->findOrFail($id);

        $shop->delete();
        return response()->json([
            'status' => 200,
            'message' => "Id=$id deleted."
        ]);
    }
}
