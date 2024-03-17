<?php

namespace App\Http\Controllers\api;

// Vendors
use App\Http\Controllers\Controller;
use Carbon\Carbon;
// Utils
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
// Controller Deps
use App\Models\User;
use App\Http\Requests\UserRequest;

class UserController extends Controller
{
    protected array $EUserRoles = [
        'user' => 'USER',
        'admin' => 'ADMIN'
    ];

    protected function tokenAbilities($userRole): array
    {
        if ($userRole == $this->EUserRoles['admin']) {
            return [
                'product-create',
                'product-update',
                'product-update'
            ];
        }
        return [];
    }

    public function get(UserRequest $request): array
    {
        return [
            'userData' => $request->user()
        ];
    }

    public function create(UserRequest $request)
    {
        $rules = [
            'email' => 'required|email|min:8|max:128',
            'password' => 'required|min:8|max:32',
            'role' => 'required|min:4|max:10'
        ];
        $data = $request->input('registerData');

        $validate = Validator::make($data, $rules);
        if ($validate->fails()) {
            return response()->json([
                'error' => [
                    'code' => '401',
                    'message' => $validate->errors(),
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
            Carbon::now()->addSeconds(14*24*60*60)
        )->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login(UserRequest $request)
    {
        $rules = [
            'email' => 'required|email|min:8|max:128',
            'password' => 'required|string|min:8|max:32'
        ];
        $data = $request->input('loginData');

        $validate = Validator::make($data, $rules);
        if ($validate->fails()) {
            return response()->json([
                'error' => [
                    'code' => 401,
                    'message' => $validate->errors()
                ]
            ], 401);
        }

        $user = User::query()->where('email' , '=', $data['email'])->first();
        if (!$user) {
            return ['user' => null];
        }

        $passwordIsCorrect = Hash::check($data['password'], $user['password']);
        if (!$passwordIsCorrect) {
            return ['user' => null];
        }

        $token = $user->createToken(
            'Bearer',
            $this->tokenAbilities($user['role']),
            Carbon::now()->addSeconds(14*24*60*60)
        )->plainTextToken;

        return response()->json(
            [
                'userData' => $user,
                'token' => $token,
            ]
        );
    }
}
