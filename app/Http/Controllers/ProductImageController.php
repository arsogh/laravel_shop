<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse
    {
        if (!$request->hasFile('path')) {
            return response()->json(['upload_file_not_found'], 400);
        }

        $allowedfileExtension = ['jpg', 'png'];
        $file = $request->file('path');

        $extension = $file->getClientOriginalExtension();

        $check = in_array($extension, $allowedfileExtension);

        if ($check) {
            $mediaFile = $request->path;

            $path = $mediaFile->store('public/productImages');
            $name = $mediaFile->getClientOriginalName();
            $prod_id = $request->product_id;

            $order = ProductImage::find($prod_id)->get()->toArray();
            $order = count($order) + 1;

            //store image file into directory and db
            $save = new ProductImage();
            $save->name = $name;
            $save->path = $path;
            $save->product_id = $prod_id;
            if ($def = $request->default) {
                $save->default = $def;
            }
            $save->order = $order;
            $save->save();

        } else {
            return response()->json(['invalid_file_format'], 422);
        }

        return response()->json(['file_uploaded'], 200);
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
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function reorder()
    {

    }
}
