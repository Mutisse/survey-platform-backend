<?php
// app/Http/Requests/PaymentRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Ajuste conforme sua lógica de autenticação
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:1|max:1000000',
            'currency' => 'sometimes|string|size:3|in:MZN,USD,ZAR',
            'customer_phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9]{9,12}$/' // Ajuste conforme formato de Moçambique
            ],
            'payment_method' => 'sometimes|string|in:mpesa,card,bank_transfer,cash',
            'metadata' => 'sometimes|array',
            'metadata.user_id' => 'sometimes|integer|exists:users,id',
            'metadata.description' => 'sometimes|string|max:255'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'O valor é obrigatório',
            'amount.min' => 'O valor mínimo é 1 MZN',
            'amount.max' => 'O valor máximo é 1.000.000 MZN',
            'customer_phone.required' => 'O telefone é obrigatório',
            'customer_phone.regex' => 'O telefone deve conter apenas números (9-12 dígitos)',
            'payment_method.in' => 'Método de pagamento inválido',
            'currency.in' => 'Moeda inválida'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Formatar número de telefone se necessário
        if ($this->has('customer_phone')) {
            $phone = preg_replace('/[^0-9]/', '', $this->customer_phone);
            $this->merge([
                'customer_phone' => $phone
            ]);
        }
    }
}
