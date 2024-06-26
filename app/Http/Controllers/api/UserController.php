<?php

namespace App\Http\Controllers\api;

// Vendors
use App\Http\Controllers\Controller;
use Carbon\Carbon;

// Utils
use App\Decorators\ValidationDecorator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\MessageBag;

// Controller Deps
use App\Models\User;
use App\Http\Requests\UserRequest;

class UserController extends Controller
{
    protected array $EUserRoles = [
        'user' => 'USER',
        'admin' => 'ADMIN',
        'owner' => 'OWNER',
    ];
    protected array $AdminAbilities = [
        'product-create',
        'product-update',
        'product-delete',
        'brand-create',
        'brand-update',
        'brand-delete'
    ];
    protected array $UserAbilities = [];
    protected ValidationDecorator $validationDecorator;

    public function __construct(ValidationDecorator $validationDecorator)
    {
        $this->validationDecorator = $validationDecorator;
    }

    protected function tokenAbilities($userRole): array
    {
        $hasAdminProperties = false;
        if ($userRole === $this->EUserRoles['admin'] || $userRole === $this->EUserRoles['owner']) {
            $hasAdminProperties = true;
        }

        if ($hasAdminProperties) {
            return $this->AdminAbilities;
        }
        return $this->UserAbilities;
    }

    public function get(): JsonResponse
    {
        $user = auth('sanctum')->user();

        return response()->json([
            'data' => $user
        ]);
    }

    public function create(UserRequest $request): JsonResponse
    {
        $rules = [
            'email' => 'required|email|min:8|max:128',
            'password' => 'required|min:8|max:32',
            'role' => 'required|min:4|max:10',
            'wishlistToken' => 'required|string|min:8',
            'cartToken' => 'required|string|min:8'
        ];

        $data = $this->validationDecorator->validate($rules, $request->input('registerData'));
        if ($data instanceof MessageBag) {
            return response()->json([
                'error' => [
                    'code' => 401,
                    'message' => $data,
                ]
            ], 401);
        }

        $userRole = $this->EUserRoles[$data['role']];
        $user = new User([
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role' => $userRole,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user->save();

        $token = $user->createToken(
            'Bearer',
            $this->tokenAbilities($userRole),
            Carbon::now()->addSeconds(14 * 24 * 60 * 60)
        )->plainTextToken;

        $wishlistIsReassigned = app('\App\Http\Controllers\api\WishlistController')
            ->reassignCartOnCreateUser($user['id'], $data['wishlistToken']);
        $cartIsReassigned = app('\App\Http\Controllers\api\CartController')
            ->reassignCartOnCreateUser($user['id'], $data['cartToken']);
        if (!$wishlistIsReassigned || !$cartIsReassigned) {
            return response()->json([
                'error' => [
                    'code' => 500,
                    'message' => [
                        'server' => 'Internal Server Error'
                    ]
                ]
            ]);
        }

        return response()->json([
            'data' => $user,
            'token' => $token,
        ]);
    }

    public function login(UserRequest $request): JsonResponse
    {
        $rules = [
            'email' => 'required|email|min:8|max:128',
            'password' => 'required|string|min:8|max:32',
        ];
        $data = $this->validationDecorator->validate($rules, $request->input('loginData'));

        if ($data instanceof MessageBag) {
            return response()->json([
                'error' => [
                    'code' => 401,
                    'message' => $data,
                ]
            ], 401);
        }

        $user = User::query()->where('email', '=', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user['password'])) {
            return response()->json([
                'error' => [
                    'code' => 500,
                    'message' => 'Email or Password incorrect'
                ]
            ], 500);
        }

        $token = $user->createToken(
            'Bearer',
            $this->tokenAbilities($user['role']),
            Carbon::now()->addSeconds(14 * 24 * 60 * 60)
        )->plainTextToken;
        $wishlistData = app('\App\Http\Controllers\api\WishlistController')
            ->getShortDataByUserId($user['id'])
            ->getData();
        $cartData = app('\App\Http\Controllers\api\CartController')
            ->getShortDataByUserId($user['id'])
            ->getData();

        return response()->json(
            [
                'data' => $user,
                'token' => $token,
                'wishlist' => $wishlistData,
                'cart' => $cartData,
            ]
        );
    }

    public function logout(): JsonResponse
    {
        $successLogout = auth('sanctum')->user()->tokens()->delete();

        return response()->json([
            'success' => boolval($successLogout),
        ]);
    }
}
