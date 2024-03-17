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
        $data = $request->input('registerData');

        if (!$data) {
            return [
                'error' => [
                    'code' => '500',
                    'message' => 'null',
                ]
            ];
        }

        $userRole = $this->EUserRoles[$data['role']];
        $user = new User([
            'email' => $data['email'],
            'password' => hash('sha256', $data['password']),
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
            return response()->json(['error' => $validate->errors()], 401);
        }

        $user = User::query()->where('email' , '=', $data['email'])->first();
        if (!$user) {
            return ['user' => null];
        }

        $passwordIsCorrect = Hash::check($data['password'], $user['password'], ['algo' => 'sha256']);
        if (!$passwordIsCorrect) {
            return ['data' => $data, 'user' => $user, 'a' => $passwordIsCorrect
            ];
        }

        $token = $user->createToken(
            'Bearer',
            $this->tokenAbilities($user['role']),
            Carbon::now()->addSeconds(14*24*60*60)
        );

        return response()->json(
            [
                'userData' => $user,
                'token' => $token,
            ]
        );
    }
}
