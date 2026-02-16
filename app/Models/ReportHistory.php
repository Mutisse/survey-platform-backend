<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportHistory extends Model
{
    protected $fillable = [
        'user_id',
        'report_type',
        'title',
        'format',
        'parameters',
        'file_path',
        'file_size',
        'generated_at'
    ];

    protected $casts = [
        'parameters' => 'array',
        'generated_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
