<?php
// app/Models/Transaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'status',
        'description',
        'payment_method',
        'account_details',
        'survey_id',
        'survey_title',
        'admin_notes',
        'completed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        
    ];

    /**
     * Relacionamento com usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com pesquisa
     */
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Escopo para transações de um usuário
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Escopo para transações concluídas
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
