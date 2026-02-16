<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipantStats extends Model
{
    use HasFactory;

    /**
     * Nome da tabela
     */
    protected $table = 'participant_stats';

    /**
     * Chave primária
     */
    protected $primaryKey = 'id';

    /**
     * Campos que podem ser preenchidos em massa
     */
    protected $fillable = [
        'user_id',
        'birth_date',
        'gender',
        'province',
        'bi_number',
        'mpesa_number',
        'occupation',
        'education_level',
        'research_interests',
        'participation_frequency',
        'consent_data_collection',
        'sms_notifications',
        'total_surveys_completed',
        'total_earnings',
        'last_survey_date',
    ];

    /**
     * Tipos dos campos
     */
    protected $casts = [
        'birth_date' => 'date',
        'research_interests' => 'array',
        'consent_data_collection' => 'boolean',
        'sms_notifications' => 'boolean',
        'total_surveys_completed' => 'integer',
        'total_earnings' => 'decimal:2',
        'last_survey_date' => 'datetime',
    ];

    /**
     * RELAÇÕES
     */

    /**
     * Relação com o usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * MÉTODOS ÚTEIS
     */

    /**
     * Verificar se o participante é estudante
     */
    public function isStudent()
    {
        return $this->occupation === 'Estudante' || !empty($this->education_level);
    }

    /**
     * Obter idade do participante
     */
    public function getAgeAttribute()
    {
        if (!$this->birth_date) {
            return null;
        }

        return $this->birth_date->age;
    }

    /**
     * Obter áreas de interesse formatadas
     */
    public function getFormattedResearchInterestsAttribute()
    {
        if (empty($this->research_interests) || !is_array($this->research_interests)) {
            return 'Nenhuma área selecionada';
        }

        $areas = [];
        $areaLabels = [
            'saude' => 'Saúde',
            'educacao' => 'Educação',
            'tecnologia' => 'Tecnologia',
            'economia' => 'Economia',
            'cultura' => 'Cultura',
            'politica' => 'Política',
            'ambiente' => 'Meio Ambiente',
            'social' => 'Questões Sociais',
        ];

        foreach ($this->research_interests as $interest) {
            if (isset($areaLabels[$interest])) {
                $areas[] = $areaLabels[$interest];
            }
        }

        return implode(', ', $areas);
    }

    /**
     * Obter província formatada
     */
    public function getProvinceNameAttribute()
    {
        $provinces = [
            'Maputo Cidade' => 'Maputo Cidade',
            'Maputo Província' => 'Maputo Província',
            'Gaza' => 'Gaza',
            'Inhambane' => 'Inhambane',
            'Sofala' => 'Sofala',
            'Manica' => 'Manica',
            'Tete' => 'Tete',
            'Zambézia' => 'Zambézia',
            'Nampula' => 'Nampula',
            'Cabo Delgado' => 'Cabo Delgado',
            'Niassa' => 'Niassa',
        ];

        return $provinces[$this->province] ?? $this->province;
    }

    /**
     * Obter ocupação formatada
     */
    public function getOccupationNameAttribute()
    {
        $occupations = [
            'Estudante' => 'Estudante',
            'Profissional' => 'Profissional',
            'Desempregado(a)' => 'Desempregado(a)',
            'Empresário(a)' => 'Empresário(a)',
            'Funcionário Público' => 'Funcionário Público',
            'Professor(a)' => 'Professor(a)',
            'Médico(a)' => 'Médico(a)',
            'Engenheiro(a)' => 'Engenheiro(a)',
            'Técnico(a)' => 'Técnico(a)',
            'Agricultor(a)' => 'Agricultor(a)',
            'Outro' => 'Outro',
        ];

        return $occupations[$this->occupation] ?? $this->occupation;
    }

    /**
     * Obter nível de educação formatado
     */
    public function getEducationLevelNameAttribute()
    {
        $levels = [
            'Ensino Primário' => 'Ensino Primário',
            'Ensino Secundário' => 'Ensino Secundário',
            'Ensino Médio' => 'Ensino Médio',
            'Ensino Técnico' => 'Ensino Técnico',
            'Ensino Superior Incompleto' => 'Ensino Superior Incompleto',
            'Ensino Superior Completo' => 'Ensino Superior Completo',
            'Pós-graduação' => 'Pós-graduação',
        ];

        return $levels[$this->education_level] ?? $this->education_level;
    }

    /**
     * Obter ganhos formatados
     */
    public function getFormattedTotalEarningsAttribute()
    {
        return number_format($this->total_earnings, 2, ',', '.') . ' MZN';
    }

    /**
     * Incrementar número de pesquisas completadas
     */
    public function incrementSurveys($amount = 1)
    {
        $this->increment('total_surveys_completed', $amount);
        $this->last_survey_date = now();
        $this->save();

        return $this;
    }

    /**
     * Adicionar ganhos
     */
    public function addEarnings($amount)
    {
        $this->increment('total_earnings', $amount);
        $this->save();

        // Também atualizar o saldo do usuário
        if ($this->user) {
            $this->user->addBalance($amount);
        }

        return $this;
    }

    /**
     * Verificar se tem consentimento de coleta de dados
     */
    public function hasConsent()
    {
        return $this->consent_data_collection === true;
    }

    /**
     * Verificar se aceita notificações SMS
     */
    public function acceptsSmsNotifications()
    {
        return $this->sms_notifications === true;
    }

    /**
     * ESCOPOS DE CONSULTA
     */

    /**
     * Escopo para participantes com consentimento
     */
    public function scopeWithConsent($query)
    {
        return $query->where('consent_data_collection', true);
    }

    /**
     * Escopo para participantes por província
     */
    public function scopeByProvince($query, $province)
    {
        return $query->where('province', $province);
    }

    /**
     * Escopo para participantes por ocupação
     */
    public function scopeByOccupation($query, $occupation)
    {
        return $query->where('occupation', $occupation);
    }

    /**
     * Escopo para participantes estudantes
     */
    public function scopeStudents($query)
    {
        return $query->where('occupation', 'Estudante');
    }

    /**
     * Escopo para participantes que aceitam SMS
     */
    public function scopeAcceptsSms($query)
    {
        return $query->where('sms_notifications', true);
    }

    /**
     * Escopo para participantes com pelo menos N pesquisas
     */
    public function scopeWithMinSurveys($query, $minSurveys)
    {
        return $query->where('total_surveys_completed', '>=', $minSurveys);
    }

    /**
     * Escopo para participantes com pesquisas recentes
     */
    public function scopeWithRecentSurveys($query, $days = 30)
    {
        return $query->where('last_survey_date', '>=', now()->subDays($days));
    }
}
