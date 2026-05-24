<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'request_id' => $this->request_id,
            'transaction_id' => $this->transaction_id ?? $this->transaction_no,
            'txn_ref' => $this->txn_ref,
            'amount' => (float) $this->amount,
            'bank_code' => $this->bank_code,
            'result_code' => $this->result_code ?? $this->response_code,
            'message' => $this->message,
            'status' => $this->status,
            'paid_at' => $this->paid_at ? $this->paid_at->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
