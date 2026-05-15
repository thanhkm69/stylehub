<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = Auth::user()->addresses()
            ->orderBy('is_default', 'desc')
            ->paginate(2);

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách địa chỉ thành công',
            'data' => AddressResource::collection($addresses),
            'pagination' => [
                'current_page' => $addresses->currentPage(),
                'last_page' => $addresses->lastPage(),
                'per_page' => $addresses->perPage(),
                'total' => $addresses->total(),
            ]
        ]);
    }

    public function store(StoreAddressRequest $request)
    {
        $user = Auth::user();
        $addressCount = $user->addresses()->count();
        
        // Luôn đặt là mặc định nếu là địa chỉ đầu tiên, hoặc nếu người dùng yêu cầu
        $isDefault = ($addressCount === 0) ? true : ($request->is_default ?? false);

        $address = $user->addresses()->create(array_merge(
            $request->validated(),
            ['is_default' => $isDefault]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Thêm địa chỉ thành công',
            'data' => new AddressResource($address)
        ]);
    }

    public function show(Address $address)
    {
        $this->authorizeOwner($address);

        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin địa chỉ thành công',
            'data' => new AddressResource($address)
        ]);
    }

    public function update(UpdateAddressRequest $request, Address $address)
    {
        $this->authorizeOwner($address);

        $data = $request->validated();
        
        // Nếu đang là địa chỉ mặc định duy nhất, không cho phép bỏ tick mặc định
        if ($address->is_default && isset($data['is_default']) && !$data['is_default']) {
            $data['is_default'] = true; 
        }

        $address->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật địa chỉ thành công',
            'data' => new AddressResource($address)
        ]);
    }

    public function destroy(Address $address)
    {
        $this->authorizeOwner($address);

        // Kiểm tra nghiêm ngặt: Nếu là địa chỉ mặc định và user vẫn còn địa chỉ khác thì không cho xóa
        if ((bool)$address->is_default && Auth::user()->addresses()->count() > 1) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa địa chỉ mặc định khi còn địa chỉ khác. Vui lòng đặt địa chỉ khác làm mặc định trước.',
                'data' => null
            ], 400);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa địa chỉ thành công',
            'data' => null
        ]);
    }

    public function setDefault(Address $address)
    {
        $this->authorizeOwner($address);

        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Đặt địa chỉ mặc định thành công',
            'data' => new AddressResource($address)
        ]);
    }

    private function authorizeOwner(Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }
    }
}
