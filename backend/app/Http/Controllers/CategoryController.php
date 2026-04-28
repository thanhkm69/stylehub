<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with('childrensRecursive')->whereNull('parent_id');

        // SEARCH
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // FILTER
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // SORT
        // $sortMap = [
        //     'name_asc' => ['name', 'asc'],
        //     'name_desc' => ['name', 'desc'],
        //     'display_asc' => ['display', 'asc'],
        //     'display_desc' => ['display', 'desc'],
        //     'created_at_asc' => ['created_at', 'asc'],
        //     'created_at_desc' => ['created_at', 'desc'],
        // ];

        // $sortKey = $request->sort ?? 'display_asc';
        // $sort = $sortMap[$sortKey] ?? $sortMap['display_asc'];

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
    }

    public function store(StoreCategoryRequest $request)
    {
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
    }

    private function findCategory($id)
    {
        return Category::with('childrensRecursive')->findOrFail($id);
    }

    private function getChildrensId($id)
    {
        $category = $this->findCategory($id);

        $ids = [$category->id];

        if ($category->childrensRecursive->count()) {
            foreach ($category->childrensRecursive as $child) {
                $ids = array_merge($ids, $this->getChildrensId($child->id));
            }
        }

        return $ids;
    }

    public function show($id)
    {
        $category = $this->findCategory($id);
        $ids = $this->getChildrensId($id);
        return response()->json([
            "success" => true,
            "message" => "Lấy chi tiết danh mục thành công",
            "ids" => $ids,
            "data" => new CategoryResource($category),
        ], 200);
    }


    public function update(UpdateCategoryRequest $request, $id)
    {
        $category = $this->findCategory($id);
        $data = $request->validated();

        $invalidIds = $this->getChildrensId($id);

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
    }

    public function destroy($id)
    {
        $category = $this->findCategory($id);
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }
        $category->delete();
        return response()->json([
            "success" => true,
            "message" => "Xóa danh mục thành công",
            "data" => null
        ], 200);
    }
}
