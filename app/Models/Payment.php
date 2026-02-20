<?php
// app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payments';

    protected $fillable = [
        'user_id',
        'payment_intent_id',
        'client_secret',
        'amount',
        'currency',
        'customer_phone',
        'payment_method',
        'provider',
        'status',
        'mpesa_reference',
        'mpesa_response_code',
        'mpesa_response_message',
        'metadata',
        'idempotency_key'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Relacionamento com usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Escopo para pagamentos bem-sucedidos
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Escopo para pagamentos pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Verifica se o pagamento foi bem-sucedido
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Verifica se o pagamento está pendente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
