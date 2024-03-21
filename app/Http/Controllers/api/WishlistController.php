<?php

namespace App\Http\Controllers\api;

// Vendors
use App\Http\Controllers\Controller;
use \Illuminate\Http\JsonResponse;
use Illuminate\Support\MessageBag;
// Utils
use Carbon\Carbon;
use App\Decorators\ValidationDecorator;
// Controller Deps
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Http\Requests\WishlistRequest;

class WishlistController extends Controller
{
    protected ValidationDecorator $validationDecorator;

    public function __construct(ValidationDecorator $validationDecorator)
    {
        $this->validationDecorator = $validationDecorator;
    }

    public function getShortData(WishlistRequest $request): JsonResponse
    {
        $wishlistToken = $request->input('token');
        $wishlistItems = WishlistItem::query()
            ->where('wishlist_token', '=', $wishlistToken)
            ->get()
            ->toArray();

        return response()->json([
            'token' => $wishlistToken,
            'items' => array_map(function ($item) {
                return [
                    'id' => $item['id'],
                    'productId' => $item['product_id'],
                    'variantId' => $item['variant_id'],
                ];
            }, $wishlistItems),
        ]);
    }

    public function getProducts(WishlistRequest $request): JsonResponse
    {
        $idsList = $this->getShortData($request)->getData();
        $productList = [];

        foreach ($idsList->items as $id) {
            $productData = app('App\Http\Controllers\api\ProductController')
                ->getProductById($id->productId)
                ->getData();

            $productList[] = [
                'product' => $productData,
                'selectedVariant' => $id->variantId
            ];
        }

        return response()->json(['items' => $productList]);
    }

    public function createCart(): JsonResponse
    {
        $userIsLogin = auth('sanctum')->user();

        $wishlistCart = new Wishlist([
            'wishlist_token' => uuid_create(),
            'is_guest_cart' => !$userIsLogin,
            'user_id' => $userIsLogin ? $userIsLogin['id'] : null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $wishlistCart->save();

        return response()->json([
            'token' => $wishlistCart['wishlist_token'],
            'isGuest' => $wishlistCart['is_guest_cart'],
            'userId' => $wishlistCart['user_id'],
        ]);
    }

    public function addItemToCart(WishlistRequest $request): JsonResponse
    {
        $rules = [
            'token' => 'required|string|min:10|max:256',
            'id' => 'required|numeric|min:1|max:10',
            'variantId' => 'nullable|numeric|min:1|max:10'
        ];
        $data = $this->validationDecorator->validate($rules, $request->input('data'));
        if ($data instanceof MessageBag) {
            return response()->json([
                'error' => [
                    'code' => 401,
                    'message' => $data,
                ]
            ], 401);
        }

        $wishlistItem = new WishlistItem([
            'wishlist_token' => $data['token'],
            'product_id' => $data['id'],
            'variant_id' => array_key_exists('variantId', $data) ? $data['variantId'] : null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $wishlistItem->save();

        return response()->json([
            'id' => $wishlistItem['id'],
            'productId' => $wishlistItem['product_id'],
            'variantId' => $wishlistItem['variant_id']
        ]);
    }

    public function removeItemFromCart(WishlistRequest $request): JsonResponse
    {
        $rules = [
            'token' => 'required|string|min:10|max:256',
            'id' => 'required|numeric|min:1|max:10',
        ];
        $data = $this->validationDecorator->validate($rules, $request->input('data'));
        if ($data instanceof MessageBag) {
            return response()->json([
                'error' => [
                    'code' => 401,
                    'message' => $data
                ]
            ], 401);
        }

        $item = WishlistItem::query()
            ->where('wishlist_token', '=', $data['token'])
            ->where('id', '=', $data['id'])
            ->first();
        $itemIsDeleted = $item->delete();

        return response()->json([
            'success' => $itemIsDeleted,
            'id' => $item['id'],
        ]);
    }
}
