<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Mail\ContactThankYouMail;
use App\Mail\NewContactNotificationMail;
use App\Mail\AdminReplyMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Exception;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $contacts = Contact::query();

            if ($request->filled('search')) {
                $contacts->where(function ($query) use ($request) {
                    $query->where('full_name', 'like', '%' . $request->search . '%')
                          ->orWhere('email', 'like', '%' . $request->search . '%')
                          ->orWhere('subject', 'like', '%' . $request->search . '%');
                });
            }

            if ($request->filled('status')) {
                $contacts->where('status', $request->status);
            }

            if ($request->filled('created_at_from') && $request->filled('created_at_to')) {
                $contacts->whereBetween('created_at', [$request->created_at_from, $request->created_at_to]);
            }

            $contacts->orderBy('created_at', 'desc');

            if ($request->has('limit')) {
                $limit = $request->input('limit');
                $contacts = $contacts->paginate((int)$limit);
            } else {
                $contacts = $contacts->get();
            }

            return ContactResource::collection($contacts)->additional([
                'success' => true,
                'message' => 'Lấy danh sách liên hệ thành công',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContactRequest $request)
    {
        try {
            $validated = $request->validated();

            $contact = Contact::create($validated);

            // Gửi email cảm ơn khách hàng
            Mail::to($contact->email)->send(new ContactThankYouMail($contact));

            // Gửi email thông báo Admin
            Mail::to(config('mail.admin_email', 'admin@stylehub.com'))->send(new NewContactNotificationMail($contact));

            return response()->json([
                'success' => true,
                'message' => 'Gửi liên hệ thành công',
                'data' => new ContactResource($contact)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin liên hệ thành công',
                'data' => new ContactResource($contact)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContactRequest $request, Contact $contact)
    {
        try {
            $validated = $request->validated();
            $oldStatus = $contact->status;

            if ($validated['status'] === 'replied' && $contact->status !== 'replied') {
                $validated['replied_at'] = now();
            }

            $contact->update($validated);

            // Gửi email trả lời nếu status thay đổi thành 'replied'
            if ($validated['status'] === 'replied' && $oldStatus !== 'replied') {
                Mail::to($contact->email)->send(new AdminReplyMail($contact, $validated['admin_note'] ?? ''));
            }

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật liên hệ thành công',
                'data' => new ContactResource($contact)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        try {
            $contact->delete();
            return response()->json([
                'success' => true,
                'message' => 'Xóa liên hệ thành công',
                'data' => null
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
