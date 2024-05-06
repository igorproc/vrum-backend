<?php

namespace App\Http\Controllers\api;

use App\Decorators\ValidationDecorator;
use App\Http\Controllers\Controller;

use App\Models\Brand;
use App\Models\Product;
use App\Http\Requests\ProductRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\MessageBag;

class ProductController extends Controller
{
    protected array $EProductTypes = [
        "base" => "BASE",
        "configurable" => "CONFIGURABLE"
    ];
    protected ValidationDecorator $validationDecorator;

    public function __construct(ValidationDecorator $validationDecorator) {
        $this->validationDecorator = $validationDecorator;
    }

    public function getProductById(int $id): JsonResponse
    {
        $productData = Product::query()->find($id);
        if (!$productData) {
            return response()->json([
                'error' => [
                    'code' => 500,
                    'message' => 'Product wasn\'t find'
                ]
            ]);
        }

        $brandData = Brand::query()->find($productData->brand_id);
        $productImage = 'https://api.specto.su' . $productData->product_image;

        $data = [
            'id' => $productData->id,
            '__typename' => $productData->__typename,
            'name' => $productData->name,
            'description' => $productData->description,
            'price' => $productData->price,
            'productImage' => $productImage,
            'brand' => [
                'id' => $brandData->id ?? null,
                'name' => $brandData->name ?? null,
            ],
            'times' => [
                'createdAt' => $productData->created_at,
                'updatedAt' => $productData->updated_at,
            ]
        ];

        if ($productData->__typename == 'BASE') {
            return response()->json($data);
        }

        $data['configurable'] = app('App\Http\Controllers\api\ConfigurableProductController')->getData($id);

        return response()->json($data);
    }

    public function getPage(ProductRequest $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $size = $request->input('size', 8);
        $brands = $request->input('brand', null);
        $sort = $request->input('sort', 'asc');
        $query = Product::query();

        if ($brands) {
            $brands = explode(',', $brands);
            $brands = array_map('intval', $brands);
            $query->whereIn('brand_id', $brands);
        }

        if ($sort === 'asc') {
            $query->orderBy('created_at');
        } else {
            $query->orderByDesc('created_at');
        }

        $productIds = $query->paginate($size, ['id'], 'page', $page);

        $productList = [];
        foreach ($productIds->items() as $product) {
            $productList[] = $this->getProductById($product->id)->getData();
        }

        return response()->json([
            'products' => $productList,
            'totalPages' => $productIds->lastPage(),
            'totalProducts' => $productIds->total()
        ]);
    }

    public function getByName(string $name): JsonResponse
    {
        $productId = Product::query()
            ->where('name', '=', $name)
            ->first();

        return $this->getProductById($productId->id);
    }

    public function create(ProductRequest $request): JsonResponse
    {
        $rules = [
            'typename' => 'required|string|min:4|max:20',
            'name' => 'required|string|min:3|max:64',
            'description' => 'required|string|min:5|max:256',
            'productImage' => 'required|string|max:128'
        ];

        $input = $this->validationDecorator->validate($rules, $request->input('productData'));
        if ($input instanceof MessageBag) {
            return response()->json([
                'error' => [
                    'code' => 401,
                    'message' => $input
                ]
            ], 401);
        }

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

        return response()->json([
            'pid' => $productData['id'],
            '__typename' => $input['typename'],
            'name' => $productData['name'],
            'description' => $productData['description'],
            'price' => $productData['price'],
            'productImage' => $productData['product_image'],
            'brandId' => $input['brandId'],
        ]);
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

    public function update(ProductRequest $request)
    {
        $rules = [
            'id' => 'required|numeric|min:1|max:100000',
            'typename' => 'nullable|string|min:4|max:20',
            'name' => 'nullable|string|min:3|max:64',
            'description' => 'nullable|string|min:5',
            'price' => 'nullable|numeric|min:1',
            'imageUrl' => 'nullable|string|max:128'
        ];
        $data = $this->validationDecorator->validate($rules, $request->input('data'));
        if ($data instanceof MessageBag) {
            return response()->json([
                'error' => [
                    'code' => 401,
                    'message' => $data
                ]
            ], 400);
        }

        if (array_key_exists('typename', $data)) {
            $data['__typename'] = $this->EProductTypes[$data['typename']];
        }
        if (array_key_exists('imageUrl', $data)) {
            $data['product_image'] = $data['imageUrl'];
        }

        $product = Product::query()->find($data['id']);
        $product->fill($data);
        $product->save();

        return response()->json(['product' => $product]);
    }
}
