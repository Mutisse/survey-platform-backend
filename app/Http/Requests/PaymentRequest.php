<?php
// app/Http/Requests/PaymentRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:1',
            'currency' => 'nullable|string|size:3',
            'customer_phone' => 'required|string|max:20',
            'payment_method' => 'nullable|string|in:mpesa,bank,card',
            'metadata' => 'nullable|array',
        ];
    }
}
