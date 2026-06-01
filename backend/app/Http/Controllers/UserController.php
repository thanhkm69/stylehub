<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Jobs\SendPasswordJob;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = User::query();

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where('name', 'LIKE', "%{$keyword}%")->orWhere('email', 'LIKE', "%{$keyword}%");
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $query->orderBy('created_at', 'desc');

        $users = $query->paginate(15);

        return UserResource::collection($users)->additional([
            'success' => true,
            'message' => 'Lấy danh sách người dùng thành công',
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $plainPassword = Str::random(10);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $plainPassword,
            'role' => $request->role ?? 'user',
            'status' => $request->status ?? true,
            'email_verified_at' => now(),
        ]);

        SendPasswordJob::dispatch($user->name, $user->email, $plainPassword);

        return response()->json([
            'success' => true,
            'message' => 'Tạo người dùng thành công. Mật khẩu đã được gửi về email.',
            'data' => new UserResource($user),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin người dùng thành công',
            'data' => new UserResource($user),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();
        if (! empty($data['password'])) {
            SendPasswordJob::dispatch($user->name, $user->email, $data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật người dùng thành công',
            'data' => new UserResource($user),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        if (strcasecmp((string) $user->role, 'admin') === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa tài khoản Admin',
                'data' => null,
            ], 403);
        }

        if (auth()->id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa chính mình',
                'data' => null,
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa người dùng thành công',
            'data' => null,
        ]);
    }
}
