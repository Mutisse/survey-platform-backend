<?php
// app/Http/Resources/PaymentResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'customer_phone' => $this->customer_phone,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'mpesa_reference' => $this->mpesa_reference,
            'client_secret' => $this->client_secret,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'user' => $this->when($this->relationLoaded('user'), function () {
                return [
                    'id' => $this->user?->id,
                    'name' => $this->user?->name,
                    'email' => $this->user?->email,
                    'phone' => $this->user?->phone,
                ];
            }),
        ];
    }
}
