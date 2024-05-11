<?php

namespace App\Http\Controllers\api;

// Vendors
use App\Http\Controllers\Controller;
use App\Mail\OrderConfimation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
// Utils
use Carbon\Carbon;
use App\Decorators\ValidationDecorator;
// Controller Deps
use App\Http\Requests\CheckoutRequest;
use App\Models\CartItem;
use App\Models\Checkout;
use App\Models\CheckoutUserData;
use App\Models\CheckoutCart;

class CheckoutController extends Controller
{
    protected ValidationDecorator $validationDecorator;

    public function __construct(ValidationDecorator $validationDecorator)
    {
        $this->validationDecorator = $validationDecorator;
    }

    protected array $EOrderStatus = [
        'pending' => 'PENDING',
        'shipping' => 'SHIPPING',
        'received' => 'RECEIVED',
        'error' => 'DECLINED',
    ];

    protected array $EOrderPayments = [
        'cash' => 'CASH',
        'online.card' => 'CARD',
        'online.BTC' => 'BTC',
    ];

    protected int $DEFAULT_PAGE_SIZE = 24;

    public function getPage (CheckoutRequest $request): JsonResponse {
        $page = $request->input('page', 1);
        $query = Checkout::query();

        $orders = $query->paginate($this->DEFAULT_PAGE_SIZE, ['*'], 'page', $page);
        $ordersList = [];

        foreach ($orders->items() as $order) {
            $ordersList[] = [
                'id' => $order->id,
                'token' => $order->order_id,
                'status' => $order->status,
                'payment' => $order->payment,
                'times' => [
                    'createdAt' => $order->created_at,
                    'updatedAt' => $order->updated_at
                ]
            ];
        }

        return response()->json([
            'orders' => $ordersList,
            'totalPages' => $orders->lastPage(),
            'totalOrders' => $orders->total()
        ]);
    }

    private function transferFromCartToOrder (int $orderId, string $cartToken): array
    {
        $cartItems = CartItem::query()
            ->where('cart_token', '=', $cartToken)
            ->get()
            ->toArray();

        foreach ($cartItems as $item) {
            $checkoutItem = new CheckoutCart([
                'checkout_id' => $orderId,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'quantity' => $item['quantity'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $checkoutItem->save();
        }

        CartItem::query()->where('cart_token', '=', $cartToken)->delete();
        return CheckoutCart::query()->where('checkout_id', '=', $orderId)->get()->toArray();
    }

    private function createOrderData (int $orderId, array $data): array
    {
        $userData = new CheckoutUserData([
            'checkout_id' => $orderId,
            'name' => $data['name'],
            'surname' => $data['surname'],
            'country' => $data['country'],
            'city' => $data['city'],
            'address' => $data['address'],
            'email' => $data['email'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $userData->save();

        return $data;
    }

    private function createOrderMail (string $email, array $products): void
    {
        $productList = [];
        foreach ($products as $product)
        {
            $productData = app('\App\Http\Controllers\api\ProductController')
                ->getProductById($product['product_id'])
                ->getData();

            $productList[] = [
                'product' => $productData,
                'qty' => $product['quantity'],
                'selectedVariant' => $product['variant_id'],
            ];
        }

        Mail::to($email)->send(new OrderConfimation($productList));
    }

    public function createOrder (CheckoutRequest $request): JsonResponse
    {
        $rules = [
            'token' => 'required|string|min:3',
            'paymentType' => 'required|string|min:2',
            'user.name' => 'required|string|min:3',
            'user.surname' => 'required|string|min:3',
            'user.country' => 'required|string|min:3',
            'user.city' => 'required|string|min:3',
            'user.address' => 'required|string|min:3',
            'user.email' => 'required|email|min:3'
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

        $checkoutData = new Checkout([
            'order_id' => substr(uniqid("#"), 0, 10),
            'cart_token' => $data['token'],
            'status' => $this->EOrderStatus['pending'],
            'payment' => $this->EOrderPayments[$data['paymentType']],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $checkoutData->save();

        $checkoutItems = $this->transferFromCartToOrder($checkoutData['id'], $data['token']);
        $checkoutUserData = $this->createOrderData($checkoutData['id'], $data['user']);
        $this->createOrderMail($data['user']['email'], $checkoutItems);

        return response()->json([
            'status' => [
                'code' => 200,
                'success' => true
            ],
            'data' => [
                'order' => $checkoutData,
                'items' => $checkoutItems,
                'data' => $checkoutUserData,
            ],
        ]);
    }

    public function updateOrderStatus (CheckoutRequest $request): JsonResponse {
        $rules = [
            'id' => 'required|numeric|min:1|max:10000',
            'status' => 'required|string|min:2',
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

        $order = Checkout::query()->find($data['id']);
        $order->update([
            'status' => $this->EOrderStatus[$data['status']]
        ]);
        $order->save();

        return response()->json([
            'item' => $order
        ]);
    }
}
