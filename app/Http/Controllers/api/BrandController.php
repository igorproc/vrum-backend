<?php

namespace App\Http\Controllers\api;

// Vendors
use App\Http\Controllers\Controller;
use Illuminate\Support\MessageBag;
// Utils
use Carbon\Carbon;
use App\Decorators\ValidationDecorator;
// Controller Deps
use App\Models\Brand;
use App\Http\Requests\BrandRequest;

class BrandController extends Controller
{
    protected ValidationDecorator $validationDecorator;

    public function __construct(ValidationDecorator $validationDecorator)
    {
        $this->validationDecorator = $validationDecorator;
    }

    public function create(BrandRequest $request)
    {
        $rules = [
            'name' => 'required|string|min:3|max:32',
            'imageUrl' => 'required|string|min:3|max:128',
        ];
        $data = $this->validationDecorator->validate($rules, $request->input('data'));

        if ($data instanceof MessageBag) {
            return response()->json([
                'error' => [
                    'code' => 401,
                    'message' => $data
                ]
            ], 500);
        }

        $brand = new Brand([
            'name' => $data['name'],
            'image_url' => $data['imageUrl'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $brand->save();

        return response()->json(['data' => $brand]);
    }

    public function delete(BrandRequest $request)
    {
        $rules = ['id' => 'numeric|min:1|max:10'];
        $requestData = $this->validationDecorator->validate($rules, $request->all());
        if ($requestData instanceof \Illuminate\Support\MessageBag) {
            return response()->json([
                'error' => [
                    'code' => 401,
                    'message' => $requestData
                ]
            ], 401);
        }

        $brand = Brand::query()->find($requestData['id']);
        $brandIsDeleted = $brand->delete();

        return [
            'brandIsDeleted' => $brandIsDeleted
        ];
    }

    public function update(BrandRequest $request)
    {
        $rules = [
            'id' => 'required|numeric|min:1|max:10',
            'name' => 'nullable|string|min:3|max:32',
            'image_url' => 'nullable|string|min:3|max:128'
        ];
        $data = $this->validationDecorator->validate($rules, $request->input('data'));

        if ($data instanceof \Illuminate\Support\MessageBag) {
            return response()->json([
                'error' => [
                    'code' => 401,
                    'message' => $data
                ]
            ], 401);
        }

        $brand = Brand::query()->find($data['id']);
        $brand->fill($data);
        $brand->save();

        return response()->json([
            'brand' => $brand
        ]);
    }
}
