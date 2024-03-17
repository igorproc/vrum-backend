<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;

use App\Models\Brand;
use App\Models\Product;
use App\Http\Requests\ProductRequest;

class ProductController extends Controller
{
    protected array $EProductTypes = [
        "base" => "BASE",
        "configurable" => "CONFIGURABLE"
    ];

    public function getProductById(int $id)
    {
        $productData = Product::query()->find($id);
        if (!$productData) {
            return [
                'error' => [
                    'code' => 500,
                    'message' => 'Product wasn\'t find'
                ]
            ];
        }

        $brandData = Brand::query()->find($productData->brand_id);
        $data = [
            '__typename' => array_search($productData->__typename, $this->EProductTypes),
            'name' => $productData->name,
            'description' => $productData->description,
            'price' => $productData->price,
            'productImage' => $productData->product_image,
            'brand' => [
                'id' => $brandData->id ?? null,
                'name' => $brandData->name ?? null,
            ],
            'createdAt' => $productData->created_at,
            'updatedAt' => $productData->updated_at,
        ];

        if ($productData->__typename == 'BASE') {
            return response()->json($data);
        }

        $data['configurable'] = app('App\Http\Controllers\api\ConfigurableProductController')->getData($id);

        return response()->json($data);
    }

    public function getAll()
    {
        $productIds = Product::all()->modelKeys() ?? [];

        $productList = [];
        foreach ($productIds as $productId) {
            array_push($productList, $this->getProductById($productId));
        }

        return $productList;
    }

    public function getByName(string $name)
    {
        $productId = Product::query()
            ->where('name', '=', $name)
            ->first();

        return $this->getProductById($productId->id);
    }

    public function create(ProductRequest $request): array
    {
        $input = $request->input('productData');
        $productType = $this->EProductTypes[$input['typename']] ?? $this->EProductTypes['base'];

        $productData = new Product([
            '__typename' => $productType,
            'name' => $input['name'],
            'description' => $input['description'],
            'price' => $input['price'],
            'product_image' => $input['productImage'],
            'brand_id' => $input['brandId'],
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $productData->save();

        return [
            'pid' => $productData['id'],
            '__typename' => $input['typename'],
            'name' => $productData['name'],
            'description' => $productData['description'],
            'price' => $productData['price'],
            'productImage' => $productData['product_image'],
            'brandId' => $input['brandId'],
        ];
    }

    public function delete(int $id) {
        $product = Product::query()->find($id);
        $deletedProduct = $product->delete();

        if ($deletedProduct) {
            return [
                'productIsDeleted' => true
            ];
        }
        return [
            'productIsDeleted' => false
        ];
    }
}
