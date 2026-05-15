<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateSelfProfileRequest;
use App\Http\Resources\ProfileResource;
use Illuminate\Http\Request;
use Exception;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        try {
            $profiles = Profile::query();

            if ($request->filled('search')) {
                $profiles->where(function ($query) use ($request) {
                    $query->where('full_name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%')
                        ->orWhere('phone', 'like', '%' . $request->search . '%')
                        ->orWhere('occupation', 'like', '%' . $request->search . '%');
                });
            }

            if ($request->filled('gender')) {
                $profiles->where('gender', $request->gender);
            }

            if ($request->filled('status')) {
                $profiles->where('status', $request->status);
            }

            $sortMap = [
                'created_at_asc' => ['created_at', 'asc'],
                'created_at_desc' => ['created_at', 'desc'],
                'full_name_asc' => ['full_name', 'asc'],
                'full_name_desc' => ['full_name', 'desc'],
            ];

            $sortKey = $request->sort ?? 'created_at_desc';
            $sort = $sortMap[$sortKey] ?? $sortMap['created_at_desc'];
            $profiles->orderBy($sort[0], $sort[1]);

            $limit = (int) $request->input('limit', 15);
            $profiles = $profiles->paginate($limit);

            return ProfileResource::collection($profiles)->additional([
                'success' => true,
                'message' => 'Lấy dữ liệu hồ sơ thành công',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách hồ sơ.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreProfileRequest $request)
    {
        try {
            $profile = Profile::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Thêm hồ sơ thành công',
                'data' => new ProfileResource($profile),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo hồ sơ.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Profile $profile)
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Lấy chi tiết hồ sơ thành công',
                'data' => new ProfileResource($profile),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy chi tiết hồ sơ.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateProfileRequest $request, Profile $profile)
    {
        try {
            $profile->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật hồ sơ thành công',
                'data' => new ProfileResource($profile),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật hồ sơ.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function me(Request $request)
    {
        try {
            $profile = $request->user()->profile;
            return response()->json([
                'success' => true,
                'message' => 'Lấy hồ sơ cá nhân thành công',
                'data' => $profile ? new ProfileResource($profile) : null,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy hồ sơ cá nhân.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateSelf(UpdateSelfProfileRequest $request)
    {
        try {
            $user = $request->user();
            $data = $request->validated();

            $profile = $user->profile;
            if ($profile) {
                $profile->update($data);
            } else {
                $profile = $user->profile()->create($data);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật hồ sơ cá nhân thành công',
                'data' => new ProfileResource($profile),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật hồ sơ cá nhân.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Profile $profile)
    {
        try {
            $profile->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa hồ sơ thành công',
                'data' => null,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa hồ sơ.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
