<?php

namespace App\Http\Controllers\api;

// Vendors
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
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

    public function getPage(BrandRequest $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $size = $request->input('size', 8);
        $brands = Brand::query()->paginate($size, ['*'], 'page', $page);

        return response()->json([
            'brands' => array_map(function ($item) {
                $imageUrl = env('APP_ENV', true) ?
                    env('APP_URL').':8000'.$item['image_url'] :
                    env('APP_URL').$item['image_url'];

                return [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'image' => $imageUrl,
                    'times' => [
                        'createdAt' => $item['created_at'],
                        'updatedAt' => $item['updated_at'],
                    ]
                ];
            }, $brands->items())
        ]);
    }

    public function create(BrandRequest $request): JsonResponse
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

    public function delete(BrandRequest $request): JsonResponse
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

        return response()->json([
            'brandIsDeleted' => $brandIsDeleted
        ]);
    }

    public function update(BrandRequest $request): JsonResponse
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
