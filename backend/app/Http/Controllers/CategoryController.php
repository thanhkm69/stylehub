<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Storage;


class CategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Category::with('childrensRecursive')->whereNull('parent_id');

            // SEARCH
            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // FILTER
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $query->orderBy('display', 'asc')->orderBy('created_at', 'desc');

            // PAGINATE
            if ($request->filled('limit')) {
                $categories = $query->paginate((int)$request->limit);
            } else {
                $categories = $query->get();
            }

            return CategoryResource::collection($categories)->additional([
                'success' => true,
                'message' => "Lấy danh sách danh mục thành công",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreCategoryRequest $request)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $data['image'] = $request->file("image")->store('uploads/categories', 'public');
            }

            $category = Category::create($data);


            return response()->json([
                'success' => true,
                'message' => "Thêm danh mục thành công",
                'data' => new CategoryResource($category)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function getChildrensId(Category $category)
    {
        $category->loadMissing('childrensRecursive');

        $ids = [$category->id];

        if ($category->childrensRecursive->count()) {
            foreach ($category->childrensRecursive as $child) {
                $ids = array_merge($ids, $this->getChildrensId($child));
            }
        }

        return $ids;
    }

    public function show(Category $category)
    {
        try {
            $category->loadMissing('childrensRecursive');
            $ids = $this->getChildrensId($category);
            return response()->json([
                "success" => true,
                "message" => "Lấy chi tiết danh mục thành công",
                "ids" => $ids,
                "data" => new CategoryResource($category),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(UpdateCategoryRequest $request, Category $category)
    {
        try {
            $data = $request->validated();

            $invalidIds = $this->getChildrensId($category);

            if (!empty($data['parent_id']) && in_array($data['parent_id'], $invalidIds)) {
                return response()->json([
                    "success" => false,
                    "message" => "Danh mục cha không hợp lệ",
                ], 400);
            }

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('uploads/categories', 'public');
                if ($category->image && Storage::disk('public')->exists($category->image)) {
                    Storage::disk('public')->delete($category->image);
                }
            }
            $category->update($data);

            return response()->json([
                "success" => true,
                "message" => "Cập nhập danh mục thành công",
                "data" => new CategoryResource($category)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Category $category)
    {
        try {
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            $category->delete();
            return response()->json([
                "success" => true,
                "message" => "Xóa danh mục thành công",
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
