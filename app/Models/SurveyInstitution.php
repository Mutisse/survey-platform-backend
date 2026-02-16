<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyInstitution extends Model
{
    protected $table = 'survey_institutions';

    protected $fillable = [
        'name',
        'abbreviation',
        'type',
        'logo_url',
        'website',
        'contact_email',
        'is_verified',
        'survey_count',
        'description',
        'address',
        'phone',
    ];

    protected $casts = [
        'survey_count' => 'integer',
        'is_verified' => 'boolean',
    ];

    // Relações
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class, 'institution', 'name');
    }

    // Métodos auxiliares
    public function incrementSurveyCount(): void
    {
        $this->increment('survey_count');
    }

    public function decrementSurveyCount(): void
    {
        $this->decrement('survey_count');
    }

    // Scope para instituições verificadas
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    // Scope para tipos específicos
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Obter as instituições mais populares
    public function scopeMostPopular($query, $limit = 10)
    {
        return $query->orderBy('survey_count', 'desc')->limit($limit);
    }
}
