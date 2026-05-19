<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Http\Resources\BannerResource;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Banner::query();

            if ($request->filled('search')) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $query->orderBy('position', 'asc')->orderBy('created_at', 'desc');

            if ($request->filled('limit')) {
                $banners = $query->paginate((int)$request->limit);
            } else {
                $banners = $query->get();
            }

            return BannerResource::collection($banners)->additional([
                'success' => true,
                'message' => "Lấy danh sách banner thành công",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreBannerRequest $request)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $data['image'] = $request->file("image")->store('uploads/banners', 'public');
            }

            $banner = Banner::create($data);

            Cache::forget('home_data');

            return response()->json([
                'success' => true,
                'message' => "Thêm banner thành công",
                'data' => new BannerResource($banner)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Banner $banner)
    {
        try {
            return response()->json([
                "success" => true,
                "message" => "Lấy chi tiết banner thành công",
                "data" => new BannerResource($banner),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateBannerRequest $request, Banner $banner)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('uploads/banners', 'public');
                if ($banner->image && Storage::disk('public')->exists($banner->image)) {
                    Storage::disk('public')->delete($banner->image);
                }
            }

            $banner->update($data);

            Cache::forget('home_data');

            return response()->json([
                "success" => true,
                "message" => "Cập nhật banner thành công",
                "data" => new BannerResource($banner)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Banner $banner)
    {
        try {
            if ($banner->image && Storage::disk('public')->exists($banner->image)) {
                Storage::disk('public')->delete($banner->image);
            }
            $banner->delete();
            
            Cache::forget('home_data');

            return response()->json([
                "success" => true,
                "message" => "Xóa banner thành công",
                "data" => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
