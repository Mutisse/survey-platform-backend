<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyResponse extends Model
{
    protected $table = 'survey_responses';

    protected $fillable = [
        'survey_id',
        'user_id',
        'answers',
        'status',
        'started_at',
        'completed_at',
        'completion_time',
        'device_type',
        'browser',
        'ip_address',
        'province',
        'city',
        'is_paid',
        'payment_amount',
        'payment_date',
        'payment_method',
        'metadata',
    ];

    protected $casts = [
        'answers' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'payment_date' => 'datetime',
        'completion_time' => 'integer',
        'payment_amount' => 'decimal:2',
        'is_paid' => 'boolean',
        'metadata' => 'array',
    ];

    // Estados possíveis
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ABANDONED = 'abandoned';

    // Relações
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false)
            ->where('status', self::STATUS_COMPLETED);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeBySurvey($query, $surveyId)
    {
        return $query->where('survey_id', $surveyId);
    }

    // Métodos auxiliares
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPaid(): bool
    {
        return $this->is_paid;
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'completion_time' => $this->started_at ? now()->diffInSeconds($this->started_at) : null,
        ]);
    }

    public function markAsPaid($amount = null, $method = null): void
    {
        $this->update([
            'is_paid' => true,
            'payment_date' => now(),
            'payment_amount' => $amount ?? $this->survey->reward,
            'payment_method' => $method,
        ]);
    }

    public function getAnswerForQuestion($questionId)
    {
        return $this->answers[$questionId] ?? null;
    }

    public function getDeviceInfo(): array
    {
        return [
            'device_type' => $this->device_type,
            'browser' => $this->browser,
            'ip_address' => $this->ip_address,
        ];
    }

    public function getLocationInfo(): array
    {
        return [
            'province' => $this->province,
            'city' => $this->city,
        ];
    }
}
