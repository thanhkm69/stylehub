<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Address;
use App\Models\Order;
use App\Models\Combo;
use App\Models\FlashSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AiChatBotController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'history' => 'nullable|array',
        ]);

        $apiKey = trim((string) config('services.groq.api_key'));
        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Hệ thống chưa cấu hình GROQ_API_KEY.'
            ], 500);
        }

        // Fetch context to make the chatbot smart about the shop
        $categories = Category::take(10)->pluck('name')->toArray();
        $products = Product::with('category')
            ->where('status', 1)
            ->latest()
            ->take(8)
            ->get();
        $coupons = Coupon::where('status', 1)
            ->where(function ($q) {
                $q->where('expires_at', '>', now())
                  ->orWhereNull('expires_at');
            })
            ->take(5)
            ->get();

        // Fetch active Combos
        $combos = Combo::with('items.product')
            ->where('status', 1)
            ->where(function ($q) {
                $q->where('starts_at', '<=', now())
                  ->where('ends_at', '>=', now())
                  ->orWhereNull('starts_at');
            })
            ->take(5)
            ->get();

        // Fetch active Flash Sales
        $flashSales = FlashSale::with(['items' => function ($q) {
                $q->where('status', 1)->with('product');
            }])
            ->where('status', 1)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->take(5)
            ->get();

        // Build system prompt with context
        $systemPrompt = "Bạn là Trợ lý ảo AI thông minh và thân thiện của StyleHub - cửa hàng thời trang trực tuyến.\n";
        $systemPrompt .= "Nhiệm vụ của bạn là hỗ trợ khách hàng mua sắm, tư vấn sản phẩm, cung cấp thông tin khuyến mãi và các chính sách của cửa hàng.\n";
        $systemPrompt .= "Hãy trả lời một cách lịch sự, thân thiện, ngắn gọn, tự nhiên và sử dụng tiếng Việt.\n\n";
        $systemPrompt .= "QUAN TRỌNG: Khi giới thiệu hoặc tư vấn một sản phẩm cụ thể từ danh sách bên dưới, bạn hãy LUÔN LUÔN chèn thẻ định dạng `[ORDER:id_sản_phẩm]` vào cuối câu giới thiệu sản phẩm để hệ thống hiển thị nút đặt hàng nhanh cho sản phẩm đó. Ví dụ: \"Sản phẩm này rất hợp với bạn [ORDER:5]\". Không chèn thẻ này cho các sản phẩm không có trong danh sách bên dưới.\n\n";
        $systemPrompt .= "LƯU Ý NGĂN CHẶN GIẢ LẬP: Bạn tuyệt đối KHÔNG ĐƯỢC tự ý phản hồi rằng 'Tôi đã đặt hàng thành công cho bạn' hay 'Đơn hàng đã được tạo thành công' hoặc bất kỳ câu khẳng định đặt hàng hoàn tất nào khác trong nội dung chat. Bạn là một mô hình ngôn ngữ và không có quyền truy cập trực tiếp để tạo đơn hàng từ văn bản của cuộc trò chuyện. Khi người dùng muốn mua hoặc đặt hàng, bạn hãy hướng dẫn họ bấm vào nút '⚡ Mua nhanh sản phẩm này' hiển thị ngay dưới phần tư vấn của bạn để tiến hành điền địa chỉ/phân loại sản phẩm và hoàn tất đặt hàng.\n\n";

        if (!empty($categories)) {
            $systemPrompt .= "Các danh mục sản phẩm hiện có:\n- " . implode("\n- ", $categories) . "\n\n";
        }

        if ($products->isNotEmpty()) {
            $systemPrompt .= "Danh sách sản phẩm mới/nổi bật tại cửa hàng:\n";
            foreach ($products as $p) {
                $categoryName = $p->category ? $p->category->name : 'Thời trang';
                $systemPrompt .= "- ID: {$p->id} | {$p->name} ({$categoryName}): Giá " . number_format($p->price) . "đ. Mô tả: {$p->description}\n";
            }
            $systemPrompt .= "\n";
        }

        if ($combos->isNotEmpty()) {
            $systemPrompt .= "Các chương trình khuyến mãi mua theo COMBO của cửa hàng:\n";
            foreach ($combos as $combo) {
                $itemNames = $combo->items->map(function ($item) {
                    return $item->product ? $item->product->name : 'Sản phẩm';
                })->filter()->toArray();
                
                $discount = $combo->discount_type === 'percent' ? "{$combo->discount_value}%" : number_format($combo->discount_value) . "đ";
                $systemPrompt .= "- Combo '{$combo->name}': Bao gồm [" . implode(", ", $itemNames) . "]. Được giảm {$discount} khi mua cả combo này. Mô tả: {$combo->description}\n";
            }
            $systemPrompt .= "\n";
        }

        if ($flashSales->isNotEmpty()) {
            $systemPrompt .= "Các sản phẩm đang chạy FLASH SALE (Giảm giá đặc biệt trong thời gian giới hạn):\n";
            foreach ($flashSales as $fs) {
                $systemPrompt .= "Chương trình '{$fs->name}':\n";
                foreach ($fs->items as $item) {
                    if ($item->product) {
                        $systemPrompt .= "  + ID: {$item->product_id} | {$item->product->name}: Giá gốc " . number_format($item->original_price) . "đ giảm chỉ còn " . number_format($item->sale_price) . "đ [ORDER:{$item->product_id}]\n";
                    }
                }
            }
            $systemPrompt .= "\n";
        }

        if ($coupons->isNotEmpty()) {
            $systemPrompt .= "Các mã giảm giá (Coupon) đang hoạt động:\n";
            foreach ($coupons as $c) {
                $discount = $c->discount_type === 'percent' ? "{$c->discount_value}%" : number_format($c->discount_value) . "đ";
                $minVal = $c->min_order_value ? "đơn hàng từ " . number_format($c->min_order_value) . "đ" : "mọi đơn hàng";
                $systemPrompt .= "- Mã '{$c->code}': Giảm {$discount} cho {$minVal}\n";
            }
            $systemPrompt .= "\n";
        }

        $systemPrompt .= "Nếu khách hàng hỏi về sản phẩm khác ngoài danh sách hoặc thông tin chính sách của cửa hàng, hãy tự tin tư vấn nhiệt tình và lịch sự. Hạn chế trả lời quá dài dòng.";

        // Construct messages payload for Groq
        $messages = [];
        $messages[] = ['role' => 'system', 'content' => $systemPrompt];

        if ($request->has('history') && is_array($request->history)) {
            foreach ($request->history as $h) {
                if (isset($h['role'], $h['content'])) {
                    $messages[] = [
                        'role' => $h['role'] === 'user' ? 'user' : 'assistant',
                        'content' => $h['content']
                    ];
                }
            }
        }

        $messages[] = ['role' => 'user', 'content' => $request->message];

        try {
            $response = Http::withToken($apiKey)
                ->post(rtrim((string) config('services.groq.base_url'), '/').'/chat/completions', [
                    'model' => config('services.groq.chat_model'),
                    'messages' => $messages,
                    'temperature' => 0.7,
                ]);

            if ($response->successful()) {
                $result = $response->json();
                $reply = $result['choices'][0]['message']['content'] ?? 'Xin lỗi, tôi gặp sự cố khi xử lý câu hỏi này.';
                return response()->json([
                    'success' => true,
                    'reply' => $reply,
                    'products' => $this->getReferencedProducts($reply),
                ]);
            }

            Log::error('Groq API Error: ' . $response->body());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối đến dịch vụ AI. Vui lòng thử lại sau.'
            ], 500);

        } catch (\Exception $e) {
            Log::error('AI Chat Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xử lý yêu cầu.'
            ], 500);
        }
    }

    private function getReferencedProducts(string $reply): array
    {
        preg_match_all('/\[ORDER:(\d+)\]/', $reply, $matches);
        $productIds = array_values(array_unique(array_map('intval', $matches[1] ?? [])));

        if (empty($productIds)) {
            return [];
        }

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->where('status', 1)
            ->get()
            ->keyBy('id');

        $pricingService = app(\App\Services\FlashSalePricingService::class);

        return collect($productIds)
            ->map(function ($productId) use ($products, $pricingService) {
                $product = $products->get($productId);

                if (! $product) {
                    return null;
                }

                $pricing = $pricingService->forListing($product);

                return [
                    'id' => $product->id,
                    'slug' => $product->slug,
                    'name' => $product->name,
                    'thumbnail' => $product->thumbnail,
                    'price' => $pricing['price'],
                    'original_price' => $pricing['original_price'],
                    'has_discount' => $pricing['price'] < $pricing['original_price'],
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function productDetails(Product $product)
    {
        $user = auth()->user();
        $flashSalePricingService = app(\App\Services\FlashSalePricingService::class);
        
        // Load active variants and their attribute values
        $product->load([
            'activeVariants.productVariantValues.attributeValue.attribute'
        ]);

        $variants = $product->activeVariants->map(function ($variant) use ($product, $flashSalePricingService) {
            $name = $variant->productVariantValues->map(function ($pvv) {
                return $pvv->attributeValue->attribute->name . ': ' . $pvv->attributeValue->value;
            })->implode(', ');
            $pricing = $flashSalePricingService->forSelection($product, $variant);
            
            return [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'stock' => $variant->stock,
                'price' => $pricing['price'],
                'original_price' => $pricing['original_price'],
                'has_discount' => $pricing['price'] < $pricing['original_price'],
                'name' => $name,
            ];
        });
        $productPricing = $flashSalePricingService->forListing($product);

        // Fetch user addresses
        $addresses = Address::where('user_id', $user->id)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $productPricing['price'],
                    'original_price' => $productPricing['original_price'],
                    'has_discount' => $productPricing['price'] < $productPricing['original_price'],
                    'thumbnail' => $product->thumbnail,
                ],
                'variants' => $variants,
                'addresses' => $addresses,
            ]
        ]);
    }

    public function quickOrder(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'address_id' => 'required|exists:addresses,id',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $product = Product::findOrFail($request->product_id);
        $address = Address::findOrFail($request->address_id);

        if ($address->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Địa chỉ không hợp lệ.'], 403);
        }

        $variant = null;
        $price = $product->price;

        if ($request->variant_id) {
            $variant = ProductVariant::with('productVariantValues.attributeValue.attribute')
                ->where('product_id', $product->id)
                ->where('status', 1)
                ->findOrFail($request->variant_id);
            
            if ($variant->stock < $request->quantity) {
                return response()->json(['success' => false, 'message' => 'Sản phẩm này đã hết hàng hoặc số lượng tồn kho không đủ.'], 422);
            }
            $price = $variant->price ?? $product->price;
        }

        try {
            $flashSaleService = app(\App\Services\FlashSalePricingService::class);
            $pricing = $flashSaleService->forSelection($product, $variant);
            $price = $pricing['price'];
        } catch (\Exception $e) {
            Log::warning('Flash sale check in quick order failed: ' . $e->getMessage());
        }

        // Calculate shipping fee
        $shippingFee = 30000;
        try {
            $ghnService = app(\App\Services\GHNService::class);
            $totalWeight = 200 * $request->quantity;
            $res = $ghnService->calculateShippingFee([
                'to_district_id' => (int) $address->district_id,
                'to_ward_code' => (string) $address->ward_code,
                'weight' => max($totalWeight, 200),
                'length' => 20,
                'width' => 20,
                'height' => 10,
                'service_type_id' => 2,
            ]);
            if ($res) {
                $shippingFee = $res['total'] ?? 30000;
            }
        } catch (\Exception $e) {
            Log::warning('Shipping fee calculation in quick order failed: ' . $e->getMessage());
        }

        DB::beginTransaction();
        try {
            $subtotal = $price * $request->quantity;
            $total = $subtotal + $shippingFee;

            $order = Order::create([
                'user_id' => $user->id,
                'order_code' => 'ORD-AI-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(3))),
                'status' => 'pending',
                'payment_method' => 'cod',
                'payment_status' => 'unpaid',
                'subtotal_amount' => $subtotal,
                'discount_amount' => 0,
                'shipping_fee' => $shippingFee,
                'total_amount' => $total,
                'shipping_info' => [
                    'name' => $user->name,
                    'phone' => $address->phone ?? $user->phone ?? '0123456789',
                    'email' => $user->email,
                    'address' => $address->address,
                    'province' => $address->province_name,
                    'district' => $address->district_name,
                    'ward' => $address->ward_name,
                ],
                'note' => $request->note ?? 'Đặt hàng nhanh qua Trợ lý AI',
            ]);

            $variantName = '';
            if ($variant) {
                $variantName = $variant->productVariantValues->map(function ($pvv) {
                    return $pvv->attributeValue->attribute->name.': '.$pvv->attributeValue->value;
                })->implode(', ');
            }

            $order->orderDetails()->create([
                'product_id' => $product->id,
                'variant_id' => $request->variant_id,
                'product_name' => $product->name,
                'variant_name' => $request->variant_id ? $variantName : null,
                'sku' => $variant ? $variant->sku : $product->sku,
                'price' => $price,
                'quantity' => $request->quantity,
                'subtotal' => $subtotal,
            ]);

            if ($variant) {
                $variant->decrement('stock', $request->quantity);
            }

            DB::commit();

            try {
                Mail::to($user->email)->send(new \App\Mail\OrderConfirmed($order));
            } catch (\Exception $e) {
                Log::error('Send confirmation email in quick order failed: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng nhanh thành công!',
                'data' => [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'total_amount' => $total,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quick order creation failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Đặt hàng thất bại. Vui lòng thử lại.'], 500);
        }
    }
}
