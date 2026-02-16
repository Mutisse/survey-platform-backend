<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'icon',
        'action_url',
        'action_label',
        'data',
        'is_read',
        'read_at',
        'expires_at',
        'priority'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'data' => 'array',
        'expires_at' => 'datetime',
        'read_at' => 'datetime'
    ];

    protected $appends = [
        'is_expired',
        'time_ago',
        'priority_label'
    ];

    // Relacionamentos
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->where('is_read', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeByPriority(Builder $query, int $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeHighPriority(Builder $query): Builder
    {
        return $query->where('priority', 3);
    }

    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeForUser(Builder $query, $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByUserType(Builder $query, string $userType): Builder
    {
        $studentTypes = [
            'survey_response', 'survey_approved', 'survey_rejected',
            'survey_expiring', 'survey_completed', 'survey_published',
            'payment_received', 'withdrawal_processed', 'withdrawal_rejected',
            'low_balance', 'research_reminder', 'deadline_alert'
        ];

        $participantTypes = [
            'survey_available', 'survey_invitation', 'response_completed',
            'payment_credited', 'profile_update', 'qualification_approved',
            'bonus_received', 'rank_improved', 'weekly_summary', 'referral_bonus'
        ];

        $adminTypes = [
            'new_user_registered', 'survey_pending_review', 'withdrawal_requested',
            'user_verification_pending', 'system_alert', 'batch_payment_processed',
            'low_system_funds', 'abuse_reported', 'high_activity'
        ];

        switch ($userType) {
            case 'student':
                return $query->whereIn('type', $studentTypes);
            case 'participant':
                return $query->whereIn('type', $participantTypes);
            case 'admin':
                return $query->whereIn('type', $adminTypes);
            default:
                return $query;
        }
    }

    // Accessors
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getTimeAgoAttribute(): string
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    public function getPriorityLabelAttribute(): string
    {
        $labels = [
            1 => 'Baixa',
            2 => 'Média',
            3 => 'Alta'
        ];

        return $labels[$this->priority] ?? 'Baixa';
    }

    public function getIconAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        // Ícones padrão baseados no tipo
        $defaultIcons = [
            'survey_response' => 'assignment_turned_in',
            'survey_approved' => 'check_circle',
            'survey_rejected' => 'cancel',
            'survey_expiring' => 'schedule',
            'payment_received' => 'payments',
            'withdrawal_processed' => 'account_balance_wallet',
            'survey_available' => 'assignment',
            'response_completed' => 'task_alt',
            'system_alert' => 'warning',
            'new_feature' => 'new_releases',
            'general_announcement' => 'campaign'
        ];

        return $defaultIcons[$this->type] ?? 'notifications';
    }

    // Métodos de instância
    public function markAsRead(): bool
    {
        return $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    public function markAsUnread(): bool
    {
        return $this->update([
            'is_read' => false,
            'read_at' => null
        ]);
    }

    public function isHighPriority(): bool
    {
        return $this->priority === 3;
    }
}
