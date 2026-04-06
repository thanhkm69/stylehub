<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterResquest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    public function register(RegisterResquest $request)
    {
        $data = $request->validated();

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        $token = $user->createToken('token', ['User'])->plainTextToken;

        return response()->json([
            "success" => true,
            "message" => "Đăng kí thành công",
            "token" => $token
        ]);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Tài khoản hoặc mật khẩu không đúng.'
            ], 401);
        }

        $user = Auth::user();

        $token = $user->createToken('token', [$user->role])->plainTextToken;

        return response()->json([
            "success" => true,
            "message" => "Đăng nhập thành công",
            "token" => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            "success" => true,
            "message" => "Đăng xuất thành công"
        ]);
    }

    public function getUser(Request $request)
    {
        return response()->json([
            "success" => true,
            "message" => "Lấy thông tin người dùng thành công",
            "data" => $request->user()
        ]);
    }
}
