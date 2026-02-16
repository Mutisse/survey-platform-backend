<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentDashboardStats extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_surveys_created',
        'active_surveys',
        'completed_surveys',
        'total_responses',
        'total_spent',
        'total_earned',
        'average_completion_rate',
        'response_rate',
        'monthly_stats',
    ];

    protected $casts = [
        'total_surveys_created' => 'integer',
        'active_surveys' => 'integer',
        'completed_surveys' => 'integer',
        'total_responses' => 'integer',
        'total_spent' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'average_completion_rate' => 'decimal:2',
        'response_rate' => 'decimal:2',
        'monthly_stats' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
