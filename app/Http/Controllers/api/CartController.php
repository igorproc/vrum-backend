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
use App\Models\Cart;
use App\Models\CartItem;
use App\Http\Requests\CartRequest;

class CartController extends Controller
{
    protected ValidationDecorator $validationDecorator;

    public function __construct(ValidationDecorator $validationDecorator)
    {
        $this->validationDecorator = $validationDecorator;
    }

    public function getShortDataByUserId(int $id): JsonResponse
    {
        $cartData = Cart::query()
            ->where('user_id', '=', $id)
            ->first();
        $request = new CartRequest([
            'token' => $cartData['cart_token']
        ]);

        return $this->getShortData($request);
    }

    public function getShortData(CartRequest $request): JsonResponse
    {
        $cartToken = $request->input('token');
        $wishlistItems = CartItem::query()
            ->where('cart_token', '=', $cartToken)
            ->get()
            ->toArray();

        return response()->json([
            'token' => $cartToken,
            'items' => array_map(function ($item) {
                return [
                    'id' => $item['id'],
                    'productId' => $item['product_id'],
                    'variantId' => $item['variant_id'],
                    'qty' => $item['quantity'],
                ];
            }, $wishlistItems),
        ]);
    }

    public function getProducts(CartRequest $request): JsonResponse
    {
        $idsList = $this->getShortData($request)->getData();
        $productList = [];

        foreach ($idsList->items as $id) {
            $productData = app('App\Http\Controllers\api\ProductController')
                ->getProductById($id->productId)
                ->getData();

            $productList[] = [
                'product' => $productData,
                'selectedVariant' => $id->variantId,
                'qty' => $id->qty,
            ];
        }

        return response()->json(['items' => $productList]);
    }

    public function createCart(): JsonResponse
    {
        $userIsLogin = auth('sanctum')->user();

        $productCart = new Cart([
            'cart_token' => uuid_create(),
            'is_guest_cart' => !$userIsLogin,
            'user_id' => $userIsLogin ? $userIsLogin['id'] : null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $productCart->save();

        return response()->json([
            'token' => $productCart['cart_token'],
            'isGuest' => $productCart['is_guest_cart'],
            'userId' => $productCart['user_id'],
        ]);
    }

    public function reassignCartOnCreateUser(int $userId, string $cartToken): bool
    {
        $cartData = Cart::query()
            ->where('cart_token', '=', $cartToken)
            ->first();

        if (!$cartData) {
            return false;
        }

        $cartData['is_guest_cart'] = false;
        $cartData['user_id'] = $userId;
        $cartData->save();

        return true;
    }

    public function addItemToCart(CartRequest $request): JsonResponse
    {
        $rules = [
            'token' => 'required|string|min:10|max:256',
            'id' => 'required|numeric|min:1|max:100000',
            'variantId' => 'nullable|numeric|min:1|max:100000',
            'qty' => 'required|numeric|min:1|max:2',
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

        $cartItem = new CartItem([
            'cart_token' => $data['token'],
            'product_id' => $data['id'],
            'variant_id' => array_key_exists('variantId', $data) ? $data['variantId'] : null,
            'quantity' => $data['qty'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $cartItem->save();

        return response()->json([
            'id' => $cartItem['id'],
            'productId' => $cartItem['product_id'],
            'variantId' => $cartItem['variant_id'],
            'qty' => $cartItem['quantity']
        ]);
    }

    public function removeItemFromCart(CartRequest $request): JsonResponse
    {
        $rules = [
            'token' => 'required|string|min:10|max:256',
            'id' => 'required|numeric|min:1|max:100000',
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

        $item = CartItem::query()
            ->where('wishlist_token', '=', $data['token'])
            ->where('id', '=', $data['id'])
            ->first();
        $itemIsDeleted = $item->delete();

        return response()->json([
            'success' => boolval($itemIsDeleted),
            'id' => $item['id'],
        ]);
    }

    public function changeItemQty(CartRequest $request): JsonResponse
    {
        $rules = [
            'token' => 'required|string|min:10|max:128',
            'id' => 'required|numeric|min:1|max:100000',
            'qty' => 'required|numeric|min:1|max:10'
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

        $cartItem = CartItem::query()
            ->where('cart_token', '=', $data['token'])
            ->where('id', '=', $data['id'])
            ->first();
        $cartItem['quantity'] = $data['qty'];
        $cartItem->save();

        return response()->json([
            'item' => [
                'id' => $cartItem['id'],
                'productId' => $cartItem['id'],
                'variantId' => $cartItem['variant_id'],
                'qty' => $cartItem['quantity']
            ]
        ]);
    }
}
