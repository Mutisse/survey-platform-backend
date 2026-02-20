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
            'amount' => (float) $this->amount,
            'formatted_amount' => $this->formatted_amount,
            'currency' => $this->currency,
            'customer_phone' => $this->customer_phone,
            'payment_method' => $this->payment_method,
            'provider' => $this->provider,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'mpesa_reference' => $this->mpesa_reference,
            'client_secret' => $this->client_secret,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // ✅ CORRIGIDO: Verificação segura do relacionamento
            'user' => $this->when($this->relationLoaded('user'), function () {
                if (!$this->user) {
                    return null;
                }

                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'phone' => $this->user->phone,
                    'role' => $this->user->role,
                ];
            }),

            // Links úteis
            'links' => [
                'self' => route('api.payments.show', $this->id),
                'status' => route('api.payments.status', $this->id),
            ],
        ];
    }
}
