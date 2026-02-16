<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportTemplate extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'report_type',
        'filters',
        'columns',
        'format',
        'schedule',
        'is_active'
    ];

    protected $casts = [
        'filters' => 'array',
        'columns' => 'array',
        'schedule' => 'array',
        'is_active' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
