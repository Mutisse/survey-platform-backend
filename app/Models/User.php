<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'balance',
        'profile_info',
        'email_notifications',
        'whatsapp_notifications',
        'verification_status',
        'verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
            'profile_info' => 'array',
            'email_notifications' => 'boolean',
            'whatsapp_notifications' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    // ==================== RELAÇÕES ====================

    public function surveys()
    {
        return $this->hasMany(Survey::class);
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function studentStats()
    {
        return $this->hasOne(StudentStats::class, 'user_id');
    }

    public function participantStats()
    {
        return $this->hasOne(ParticipantStats::class, 'user_id');
    }

    public function documents()
    {
        return $this->hasMany(StudentDocument::class, 'user_id');
    }

    // ==================== MÉTODOS DE VERIFICAÇÃO ====================

    public function isStudent()
    {
        return $this->role === 'student';
    }

    public function isParticipant()
    {
        return $this->role === 'participant';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isVerified()
    {
        return $this->verification_status === 'approved';
    }

    public function isPendingVerification()
    {
        return $this->verification_status === 'pending';
    }

    public function isRejected()
    {
        return $this->verification_status === 'rejected';
    }

    // ==================== MÉTODOS DO DASHBOARD ====================

    /**
     * Obter estatísticas do dashboard do estudante
     */
    public function getDashboardStats()
    {
        if (!$this->isStudent()) {
            return null;
        }

        // Se tiver estatísticas salvas, retorna elas
        if ($this->studentDashboardStats()) {
            return $this->studentDashboardStats;
        }

        // Calcula estatísticas em tempo real
        return $this->calculateDashboardStats();
    }

    /**
     * Calcular estatísticas do dashboard em tempo real
     */
    private function calculateDashboardStats()
    {
        // Carrega todas as pesquisas com contagem de respostas
        $surveys = $this->surveys()->withCount('responses')->get();

        // Cálculos básicos
        $totalResponses = $surveys->sum('responses_count');
        $totalSurveys = $surveys->count();
        $completedSurveys = $surveys->where('status', 'completed')->count();
        $activeSurveys = $surveys->where('status', 'active')->count();
        $totalSpent = (float) $surveys->sum('total_cost');
        $totalTargetResponses = $surveys->sum('target_responses');

        // Cálculo de porcentagens
        $completionRate = $totalSurveys > 0
            ? round(($completedSurveys / $totalSurveys) * 100, 1)
            : 0;

        $responseRate = $totalTargetResponses > 0
            ? round(($totalResponses / $totalTargetResponses) * 100, 1)
            : 0;

        return [
            'total_surveys_created' => $totalSurveys,
            'active_surveys' => $activeSurveys,
            'completed_surveys' => $completedSurveys,
            'total_responses' => $totalResponses,
            'total_spent' => $totalSpent,
            'total_earned' => (float) $this->balance,
            'average_completion_rate' => $completionRate,
            'response_rate' => $responseRate,
        ];
    }

    /**
     * Calcular total de respostas recebidas
     */
    public function totalResponsesReceived()
    {
        return $this->surveys()->withCount('responses')->get()->sum('responses_count');
    }

    /**
     * Calcular total gasto em pesquisas
     */
    public function totalAmountSpent()
    {
        return (float) $this->surveys()->sum('total_cost');
    }

    /**
     * Calcular taxa de conclusão
     */
    public function calculateCompletionRate()
    {
        $totalSurveys = $this->surveys()->count();
        $completedSurveys = $this->surveys()->where('status', 'completed')->count();

        if ($totalSurveys === 0) {
            return 0;
        }

        return round(($completedSurveys / $totalSurveys) * 100, 1);
    }

    /**
     * Calcular taxa de resposta
     */
    public function calculateResponseRate()
    {
        $totalTargetResponses = $this->surveys()->sum('target_responses');
        $actualResponses = $this->totalResponsesReceived();

        if ($totalTargetResponses === 0) {
            return 0;
        }

        return round(($actualResponses / $totalTargetResponses) * 100, 1);
    }

    /**
     * Obter pesquisas recentes
     */
    public function getRecentSurveys($limit = 5)
    {
        return $this->surveys()
            ->withCount('responses')
            ->latest()
            ->take($limit)
            ->get()
            ->map(function ($survey) {
                return [
                    'id' => $survey->id,
                    'title' => $survey->title,
                    'description' => $survey->description,
                    'category' => $survey->category,
                    'status' => $survey->status,
                    'target_responses' => $survey->target_responses,
                    'current_responses' => $survey->responses_count,
                    'reward_per_response' => $survey->reward_per_response,
                    'total_cost' => $survey->total_cost,
                    'created_at' => $survey->created_at->toISOString(),
                    'updated_at' => $survey->updated_at->toISOString(),
                ];
            });
    }

    /**
     * Obter dados do dashboard formatados para API
     */
    public function getDashboardData()
    {
        return [
            'stats' => $this->getDashboardStats(),
            'recent_surveys' => $this->getRecentSurveys(),
            'pending_withdrawals' => $this->getPendingWithdrawals(),
            'recent_earnings' => $this->getRecentEarnings(),
            'notifications' => $this->getUnreadNotifications(),
            'last_updated' => now()->toISOString(),
        ];
    }

    // ==================== MÉTODOS AUXILIARES ====================

    /**
     * Obter saques pendentes (simplificado)
     */
    public function getPendingWithdrawals()
    {
        // Se existir o modelo, usa. Senão, retorna vazio.
        if (class_exists('App\Models\WithdrawalRequest')) {
            return $this->hasMany(\App\Models\WithdrawalRequest::class)
                ->whereIn('status', ['pending', 'processing'])
                ->latest()
                ->get();
        }

        return collect();
    }

    /**
     * Obter ganhos recentes (simplificado)
     */
    public function getRecentEarnings($limit = 10)
    {
        if (class_exists('App\Models\WithdrawalRequest')) {
            return $this->hasMany(\App\Models\WithdrawalRequest::class)
                ->where('status', 'paid')
                ->latest()
                ->take($limit)
                ->get();
        }

        return collect();
    }

    /**
     * Obter notificações não lidas (simplificado)
     */
    public function getUnreadNotifications($limit = 20)
    {
        if (class_exists('App\Models\Notification')) {
            return $this->hasMany(\App\Models\Notification::class)
                ->where('is_read', false)
                ->latest()
                ->take($limit)
                ->get();
        }

        return collect();
    }

    /**
     * Verificar se pode solicitar saque
     */
    public function canRequestWithdrawal($amount = null)
    {
        $minAmount = 50;

        if ($amount !== null) {
            return $this->balance >= $amount && $amount >= $minAmount;
        }

        return $this->balance >= $minAmount;
    }

    // ==================== MÉTODOS EXISTENTES (mantidos) ====================

    public function getFormattedBalanceAttribute()
    {
        return number_format($this->balance, 2, ',', '.') . ' MZN';
    }

    public function getParticipantProfileAttribute()
    {
        if (!$this->isParticipant()) {
            return null;
        }

        if ($this->participantStats) {
            return $this->participantStats;
        }

        return $this->profile_info['participant_data'] ?? null;
    }

    public function getStudentProfileAttribute()
    {
        if (!$this->isStudent()) {
            return null;
        }

        return $this->studentStats;
    }

    /**
     * ESCOPOS DE CONSULTA
     */
    public function scopeStudents($query)
    {
        return $query->where('role', 'student');
    }

    public function scopeParticipants($query)
    {
        return $query->where('role', 'participant');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'approved');
    }

    public function scopePendingVerification($query)
    {
        return $query->where('verification_status', 'pending');
    }

    public function scopeByUniversity($query, $university)
    {
        return $query->whereHas('studentStats', function($q) use ($university) {
            $q->where('university', $university);
        });
    }

    public function scopeByCourse($query, $course)
    {
        return $query->whereHas('studentStats', function($q) use ($course) {
            $q->where('course', $course);
        });
    }

    public function scopeByProvince($query, $province)
    {
        return $query->whereHas('participantStats', function($q) use ($province) {
            $q->where('province', $province);
        });
    }

    public function scopeByOccupation($query, $occupation)
    {
        return $query->whereHas('participantStats', function($q) use ($occupation) {
            $q->where('occupation', $occupation);
        });
    }

    /**
     * MÉTODOS DE NEGÓCIO
     */
    public function addBalance($amount)
    {
        $this->increment('balance', $amount);
        return $this;
    }

    public function deductBalance($amount)
    {
        if ($this->balance < $amount) {
            throw new \Exception('Saldo insuficiente');
        }

        $this->decrement('balance', $amount);
        return $this;
    }

    public function verify()
    {
        $this->update([
            'verification_status' => 'approved',
            'verified_at' => now(),
        ]);

        return $this;
    }

    public function reject($reason = null)
    {
        $this->update([
            'verification_status' => 'rejected',
            'profile_info' => array_merge($this->profile_info ?? [], [
                'rejection_reason' => $reason,
            ]),
        ]);

        return $this;
    }

    public function markDocumentsSubmitted()
    {
        if ($this->studentStats) {
            $this->studentStats->update(['documents_submitted' => true]);
        }

        return $this;
    }

    public function incrementSurveysCompleted($amount = 1)
    {
        if ($this->isParticipant() && $this->participantStats) {
            $this->participantStats->incrementSurveys($amount);
        }

        return $this;
    }

    public function addEarnings($amount)
    {
        if ($this->isParticipant() && $this->participantStats) {
            $this->participantStats->addEarnings($amount);
        } else {
            $this->addBalance($amount);
        }

        return $this;
    }

    public function getStatsAttribute()
    {
        if ($this->isStudent()) {
            return $this->studentStats;
        } elseif ($this->isParticipant()) {
            return $this->participantStats;
        }

        return null;
    }

    public function hasDataCollectionConsent()
    {
        if ($this->isParticipant() && $this->participantStats) {
            return $this->participantStats->hasConsent();
        }

        return false;
    }

    public function getMpesaNumberAttribute()
    {
        if ($this->isParticipant() && $this->participantStats) {
            return $this->participantStats->mpesa_number;
        }

        return null;
    }

    public function getOccupationAttribute()
    {
        if ($this->isParticipant() && $this->participantStats) {
            return $this->participantStats->occupation;
        }

        return null;
    }

    public function getProvinceAttribute()
    {
        if ($this->isParticipant() && $this->participantStats) {
            return $this->participantStats->province;
        }

        return null;
    }

    public function getUniversityAttribute()
    {
        if ($this->isStudent() && $this->studentStats) {
            return $this->studentStats->university;
        }

        if ($this->profile_info && isset($this->profile_info['student_data']['university'])) {
            return $this->profile_info['student_data']['university'];
        }

        return null;
    }

    public function getCourseAttribute()
    {
        if ($this->isStudent() && $this->studentStats) {
            return $this->studentStats->course;
        }

        if ($this->profile_info && isset($this->profile_info['student_data']['course'])) {
            return $this->profile_info['student_data']['course'];
        }

        return null;
    }

    // ==================== NOVAS RELAÇÕES (opcionais) ====================

    /**
     * Notificações do usuário (opcional)
     */
    public function notifications()
    {
        if (class_exists('App\Models\Notification')) {
            return $this->hasMany(\App\Models\Notification::class);
        }

        return $this->hasMany(\App\Models\Notification::class)->where('id', 0);
    }

    /**
     * Solicitações de saque (opcional)
     */
    public function withdrawalRequests()
    {
        if (class_exists('App\Models\WithdrawalRequest')) {
            return $this->hasMany(\App\Models\WithdrawalRequest::class);
        }

        return $this->hasMany(\App\Models\WithdrawalRequest::class)->where('id', 0);
    }

    /**
     * Estatísticas do dashboard (opcional)
     */
    public function studentDashboardStats()
    {
        if (class_exists('App\Models\StudentDashboardStats')) {
            return $this->hasOne(\App\Models\StudentDashboardStats::class, 'user_id');
        }

        return null;
    }

    /**
     * Ganhos do estudante (opcional)
     */
    public function earnings()
    {
        if (class_exists('App\Models\StudentEarning')) {
            return $this->hasMany(\App\Models\StudentEarning::class);
        }

        return $this->hasMany(\App\Models\StudentEarning::class)->where('id', 0);
    }
}
