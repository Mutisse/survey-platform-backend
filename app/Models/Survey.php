<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Survey extends Model
{
    use SoftDeletes;

    protected $table = 'surveys';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'institution',
        'duration',
        'reward',
        'requirements',
        'target_responses',
        'current_responses',
        'status',
        'settings',
        'config',
        'researcher_id',
        'published_at',
        'responses_count',
    ];

    protected $casts = [
        'requirements' => 'array',
        'settings' => 'array',
        'config' => 'array',
        'reward' => 'decimal:2',
        'duration' => 'integer',
        'target_responses' => 'integer',
        'current_responses' => 'integer',
        'responses_count' => 'integer',
        'published_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'draft',
        'current_responses' => 0,
        'responses_count' => 0,
    ];

    protected $dates = [
        'published_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Relações
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function researcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'researcher_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function categoryRelation(): BelongsTo
    {
        return $this->belongsTo(SurveyCategory::class, 'category', 'name');
    }

    public function institutionRelation(): BelongsTo
    {
        return $this->belongsTo(SurveyInstitution::class, 'institution', 'name');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'active')
            ->where(function($q) {
                $q->whereColumn('current_responses', '<', 'target_responses')
                  ->orWhereNull('target_responses');
            });
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Métodos auxiliares
    public function isAvailable(): bool
    {
        return $this->status === 'active'
            && ($this->current_responses < $this->target_responses || $this->target_responses === 0);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function getCompletionRate(): float
    {
        if ($this->target_responses === 0) {
            return 0;
        }

        return ($this->current_responses / $this->target_responses) * 100;
    }

    public function incrementResponses(): void
    {
        $this->increment('current_responses');
        $this->increment('responses_count');

        // Verificar se atingiu o alvo
        if ($this->target_responses > 0 && $this->current_responses >= $this->target_responses) {
            $this->update(['status' => 'completed']);
        }
    }

    public function decrementResponses(): void
    {
        $this->decrement('current_responses');
        $this->decrement('responses_count');
    }

    public function getRequirementsArray(): array
    {
        return $this->requirements ?? [];
    }

    public function getEstimatedEarnings(): float
    {
        return $this->reward * $this->target_responses;
    }

    public function getTotalEarned(): float
    {
        return $this->reward * $this->current_responses;
    }

    public function getRemainingResponses(): int
    {
        return max(0, $this->target_responses - $this->current_responses);
    }

    // Configurações
    public function getConfig(string $key = null, $default = null)
    {
        if (is_string($this->config)) {
            $config = json_decode($this->config, true);
        } else {
            $config = $this->config ?? [];
        }

        if ($key === null) {
            return $config;
        }

        return $config[$key] ?? $default;
    }

    public function setConfig(string $key, $value): void
    {
        $config = $this->getConfig();
        $config[$key] = $value;
        $this->config = $config;
        $this->save();
    }

    // Publish survey
    public function publish(): void
    {
        $this->update([
            'status' => 'active',
            'published_at' => now(),
        ]);
    }

    // Archive survey
    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    // Duplicate survey
    public function duplicate(): Survey
    {
        $newSurvey = $this->replicate();
        $newSurvey->title = $this->title . ' (Cópia)';
        $newSurvey->status = 'draft';
        $newSurvey->published_at = null;
        $newSurvey->current_responses = 0;
        $newSurvey->responses_count = 0;
        $newSurvey->save();

        // Duplicate questions
        foreach ($this->questions as $question) {
            $newQuestion = $question->replicate();
            $newQuestion->survey_id = $newSurvey->id;
            $newQuestion->save();
        }

        return $newSurvey;
    }
}
