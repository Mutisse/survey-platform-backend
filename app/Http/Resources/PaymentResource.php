<?php
// app/Http/Resources/PaymentResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_intent_id' => $this->payment_intent_id,
            'client_secret' => $this->client_secret,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'customer_phone' => $this->customer_phone,
            'payment_method' => $this->payment_method,
            'provider' => $this->provider,
            'status' => $this->status,
            'mpesa_reference' => $this->mpesa_reference,
            'mpesa_response_code' => $this->mpesa_response_code,
            'mpesa_response_message' => $this->mpesa_response_message,
            'metadata' => $this->metadata,
            'idempotency_key' => $this->idempotency_key,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relacionamentos (opcional)
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email
                ];
            }),
        ];
    }
}
