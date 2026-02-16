<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SurveyCategory extends Model
{
    protected $table = 'survey_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'survey_count',
        'is_active',
        'order',
    ];

    protected $casts = [
        'survey_count' => 'integer',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    // Relações
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class, 'category', 'name');
    }

    // Eventos
    protected static function booted(): void
    {
        static::saving(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
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

    // Scope para categorias ativas
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
