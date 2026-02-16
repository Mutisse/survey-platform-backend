<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StudentStats extends Model
{
    use HasFactory;

    /**
     * CORREÇÃO CRÍTICA - DEFINIR TABELA E CHAVE PRIMÁRIA
     */
    protected $table = 'student_stats';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'bi_number',
        'birth_date',
        'gender',
        'institution_type',
        'university',
        'course',
        'admission_year',
        'expected_graduation',
        'academic_level',
        'student_card_number',
        'research_interests',
        'documents_submitted',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'admission_year' => 'integer',
            'expected_graduation' => 'integer',
            'research_interests' => 'array',
            'documents_submitted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * RELAÇÕES
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * ACESSORES
     */
    public function getFormattedBirthDateAttribute()
    {
        if (!$this->birth_date) {
            return null;
        }

        return $this->birth_date->format('d/m/Y');
    }

    public function getAgeAttribute()
    {
        if (!$this->birth_date) {
            return null;
        }

        return now()->diffInYears($this->birth_date);
    }

    public function getAcademicStatusAttribute()
    {
        if (!$this->admission_year || !$this->expected_graduation) {
            return 'Não informado';
        }

        $currentYear = now()->year;

        if ($currentYear < $this->admission_year) {
            return 'Pré-ingresso';
        } elseif ($currentYear > $this->expected_graduation) {
            return 'Graduado';
        } else {
            $year = $currentYear - $this->admission_year + 1;
            return "{$year}º Ano";
        }
    }

    public function getAcademicProgressAttribute()
    {
        if (!$this->admission_year || !$this->expected_graduation) {
            return null;
        }

        $totalYears = $this->expected_graduation - $this->admission_year;
        $yearsCompleted = now()->year - $this->admission_year;

        if ($totalYears <= 0) {
            return 100;
        }

        $progress = ($yearsCompleted / $totalYears) * 100;
        return min(100, max(0, $progress));
    }

    public function getIsActiveStudentAttribute()
    {
        if (!$this->admission_year || !$this->expected_graduation) {
            return false;
        }

        $currentYear = now()->year;
        return $currentYear >= $this->admission_year && $currentYear <= $this->expected_graduation;
    }

    /**
     * ESCOPOS DE CONSULTA
     */
    public function scopeByUniversity($query, $university)
    {
        return $query->where('university', $university);
    }

    public function scopeByCourse($query, $course)
    {
        return $query->where('course', $course);
    }

    public function scopeByInstitutionType($query, $type)
    {
        return $query->where('institution_type', $type);
    }

    public function scopeByAcademicLevel($query, $level)
    {
        return $query->where('academic_level', $level);
    }

    public function scopeWithDocumentsSubmitted($query)
    {
        return $query->where('documents_submitted', true);
    }

    public function scopeWithoutDocuments($query)
    {
        return $query->where('documents_submitted', false);
    }

    public function scopeActiveStudents($query)
    {
        $currentYear = now()->year;
        return $query->where('admission_year', '<=', $currentYear)
            ->where('expected_graduation', '>=', $currentYear);
    }

    public function scopeGraduated($query)
    {
        $currentYear = now()->year;
        return $query->where('expected_graduation', '<', $currentYear);
    }

    /**
     * MÉTODOS DE VALIDAÇÃO
     */
    public function isValidBiNumber()
    {
        if (!$this->bi_number) {
            return false;
        }

        return preg_match('/^\d{12}[A-Z]$/', $this->bi_number);
    }

    public function isValidStudent()
    {
        return $this->university && $this->course && $this->isValidBiNumber();
    }

    /**
     * MÉTODOS DE NEGÓCIO
     */
    public function markDocumentsSubmitted()
    {
        $this->update(['documents_submitted' => true]);
        return $this;
    }

    public function updateAcademicInfo($data)
    {
        $allowedFields = [
            'university',
            'course',
            'institution_type',
            'academic_level',
            'admission_year',
            'expected_graduation',
            'student_card_number'
        ];

        $filteredData = array_intersect_key($data, array_flip($allowedFields));

        $this->update($filteredData);
        return $this;
    }

    public function updateResearchInterests($interests)
    {
        if (is_array($interests)) {
            $this->update(['research_interests' => $interests]);
        }
        return $this;
    }

    /**
     * VALIDAÇÕES
     */
    public static function validateBiNumber($biNumber)
    {
        return preg_match('/^\d{12}[A-Z]$/', $biNumber);
    }

    /**
     * ESTATÍSTICAS
     */
    public static function getUniversityStats()
    {
        return self::select('university', DB::raw('count(*) as total'))
            ->groupBy('university')
            ->orderBy('total', 'desc')
            ->get();
    }

    public static function getCourseStats()
    {
        return self::select('course', DB::raw('count(*) as total'))
            ->groupBy('course')
            ->orderBy('total', 'desc')
            ->get();
    }
}
