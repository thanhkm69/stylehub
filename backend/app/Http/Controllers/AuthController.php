<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterResquest;
use App\Http\Requests\SendVerifyEmailOtpRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\SendPasswordResetOtpRequest;
use App\Http\Requests\VerifyPasswordResetOtpRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\PasswordResetOtp;
use Illuminate\Support\Carbon;
use App\Jobs\SendOTPJob;


class AuthController extends Controller
{
    public function sendOtp($email)
    {
        $record = PasswordResetOtp::where('email', $email)->first();

        if ($record && $record->expires_at->isFuture()) {
            return [
                "success" => false,
                "message" => "Vui lòng chờ 1 phút trước khi yêu cầu OTP mới",
                "errors" => [
                    "otp" => ["Vui lòng chờ 1 phút trước khi yêu cầu OTP mới"]
                ]
            ];
        }

        PasswordResetOtp::where('email', $email)->delete();

        $otp = rand(100000, 999999);

        $result = SendOTPJob::dispatch($email, $otp);

        if (!$result) {
            return [
                "success" => false,
                "message" => "Gửi OTP thất bại",
                "errors" => [
                    "otp" => ["Gửi OTP thất bại"]
                ]
            ];
        }

        PasswordResetOtp::create([
            'email' => $email,
            'otp' => Hash::make($otp),
            'expires_at' => Carbon::now()->addMinutes(config('otp.expire_minutes', 1)),
        ]);

        return [
            "success" => true,
            "message" => "Mã OTP đã được gửi đến email của bạn"
        ];
    }

    private function verifyOtp($email, $otp)
    {
        $record = PasswordResetOtp::where('email', $email)->first();

        if (!$record) {
            return [
                "success" => false,
                "message" => "OTP không tồn tại",
                "errors" => [
                    "otp" => ["OTP không tồn tại"]
                ]
            ];
        }

        if ($record->expires_at->isPast()) {
            $record->delete();

            return [
                "success" => false,
                "message" => "OTP đã hết hạn",
                "errors" => [
                    "otp" => ["OTP đã hết hạn"]
                ]
            ];
        }

        if (!Hash::check($otp, $record->otp)) {
            return [
                "success" => false,
                "message" => "OTP không hợp lệ",
                "errors" => [
                    "otp" => ["OTP không hợp lệ"]
                ]
            ];
        }

        return ["success" => true, "message" => "OTP hợp lệ", "data" => $record];
    }

    public function sendVerifyEmailOtp(SendVerifyEmailOtpRequest $request)
    {
        $data = $request->validated();

        $result = $this->sendOtp($data['email']);

        if ($result['success']) {
            return response()->json([
                "success" => true,
                "message" => $result['message']
            ]);
        } else {
            return response()->json([
                "success" => false,
                "message" => $result['message'],
                "errors" => $result['errors']
            ], 429);
        }
    }

    public function register(RegisterResquest $request)
    {
        $data = $request->validated();

        $result = $this->verifyOtp($data['email'], $data['otp']);

        if (!$result['success']) {
            return response()->json([
                "success" => false,
                "message" => $result['message'],
                "errors" => $result['errors']
            ], 400);
        }

        $data['password'] = Hash::make($data['password']);
        $data['email_verified_at'] = Carbon::now();

        $user = User::create($data);

        $result['data']->delete();

        $token = $user->createToken('token', ['User'])->plainTextToken;

        return response()->json([
            "success" => true,
            "message" => "Đăng kí thành công",
            "token" => $token
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return response()->json([
                "success" => false,
                "message" => "Email hoặc mật khẩu không đúng.",
                "errors" => [
                    "email" => ["Email hoặc mật khẩu không đúng."],
                    "password" => ["Email hoặc mật khẩu không đúng."]
                ]
            ], 401);
        }

        $user = Auth::user();

        $token = $user->createToken('token', [$user->role])->plainTextToken;

        return response()->json([
            "success" => true,
            "message" => "Đăng nhập thành công",
            "token" => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            "success" => true,
            "message" => "Đăng xuất thành công"
        ], 200);
    }

    public function getUser(Request $request)
    {
        return response()->json([
            "success" => true,
            "message" => "Lấy thông tin người dùng thành công",
            "data" => $request->user()
        ], 200);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();

        if (!Hash::check($request->currentPassword, $user->password)) {
            return response()->json([
                "success" => false,
                "message" => "Mật khẩu hiện tại không đúng",
                "errors" => [
                    "currentPassword" => ["Mật khẩu hiện tại không đúng"]
                ]
            ], 400);
        }

        $user->password = Hash::make($request->newPassword);
        $user->save();
        $user->tokens()->delete();

        return response()->json([
            "success" => true,
            "message" => "Đổi mật khẩu thành công"
        ], 200);
    }

    public function sendPasswordResetOtp(SendPasswordResetOtpRequest $request)
    {
        $data = $request->validated();

        $result = $this->sendOtp($data['email']);

        if ($result['success']) {
            return response()->json([
                "success" => true,
                "message" => $result['message']
            ]);
        } else {
            return response()->json([
                "success" => false,
                "message" => $result['message'],
                "errors" => $result['errors']
            ], 400);
        }
    }

    public function verifyPasswordResetOtp(VerifyPasswordResetOtpRequest $request)
    {
        $data = $request->validated();

        $result = $this->verifyOtp($data['email'], $data['otp']);

        if ($result['success']) {
            return response()->json([
                "success" => true,
                "message" => $result['message']
            ]);
        } else {
            return response()->json([
                "success" => false,
                "message" => $result['message'],
                "errors" => $result['errors']
            ], 400);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return response()->json([
                "success" => false,
                "message" => "Người dùng không tồn tại",
                "errors" => [
                    "email" => ["Người dùng không tồn tại"]
                ]
            ], 404);
        }

        $result = $this->verifyOtp($data['email'], $data['otp']);

        if (!$result['success']) {
            return response()->json([
                "success" => false,
                "message" => $result['message'],
                "errors" => $result['errors']
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->newPassword)
        ]);

        $result['data']->delete();

        return response()->json([
            "success" => true,
            "message" => "Đặt lại mật khẩu thành công"
        ]);
    }
}
