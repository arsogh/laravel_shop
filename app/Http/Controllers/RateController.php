<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Rate;
use App\Models\User;
use http\Env\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $rates = Rate::query()
            ->when($user->isSeller(), function ($query) use ($user) {
                $query->whereIn('product_id', $user->products()->pluck('products.id'));
            })
            ->when($user->isAdmin(), function ($query) {
                $query->where('report_status', 1);
            })
            ->when($user->isBuyer(), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->get();

        return response()->json([
            'data' => $rates
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $order = Order::findOrFail($request->order_id);
        $prod = json_decode($order->products);

        if (count($prod) === count($request->rate) && auth()->id() === $order->user_id && $order->rated === 0) {
            for ($i = 0; $i < count($prod); $i++) {
                $save = new Rate();
                $save->user_id = $order->user_id;
                $prodArr = (array)$prod[$i];
                $save->product_id = $prodArr['id'];
                $save->order_id = $order->id;
                $save->rate = $request->rate[$i];
                if ($comm = $request->comment) {
                    $save->comment = $comm;
                }
                $save->save();
            }

            $order->update([
                'rated' => 1
            ]);

            return response()->json([
                'data' => 'Rate(s) added.'
            ], 200);
        } else {
            return response()->json([
                'data' => 'Count of rates and order products not matching or this order already have rate or this is not your order.'
            ], 200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        Rate::findOrFail($id)->update([
            'rate' => $request->rate
        ]);

        return response()->json([
            'message' => 'Rate updated.'
        ]);
    }

    /**
     * Method for seller to report rate.
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function report(Request $request, $id): JsonResponse
    {
        Rate::findOrFail($id)->update([
            'report_status' => 1,
            'report_comment' => $request->report_comment
        ]);

        return response()->json([
            'message' => 'Rate reported.',
        ]);
    }

    /**
     * Method for admin make decisions for reports.
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function reported_rates(Request $request, $id): JsonResponse
    {
        $rate = Rate::findOrFail($id);
        if ($rate->report_status === 1) {
            if ($request->status == 2) {
                $rate->update([
                    'report_status' => $request->status,
                    'report_comment' => $request->comment
                ]);
            } else {
                $rate->delete();
            }
        }

        return response()->json([
            'message' => 'Admin made his decision.',
        ]);
    }
}
