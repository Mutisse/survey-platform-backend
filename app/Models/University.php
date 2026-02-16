<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    use HasFactory;

    /**
     * Nome da tabela (opcional, Laravel detecta automaticamente)
     */
    protected $table = 'universities';

    /**
     * Campos que podem ser preenchidos em massa
     * Apenas as colunas que EXISTEM na sua tabela
     */
    protected $fillable = [
        'name',          // varchar(191) NOT NULL
        'acronym',       // varchar(191) NULL
        'type',          // varchar(191) NOT NULL
        'location',      // varchar(191) NULL
        'website',       // varchar(191) NULL
        'order',         // int NOT NULL DEFAULT 0
        // created_at e updated_at são automáticos
    ];

    /**
     * Conversões de tipo de dados
     */
    protected $casts = [
        'order' => 'integer',  // Converter para inteiro
    ];

    /**
     * Campos de data automáticos
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * RELAÇÕES
     */

    /**
     * Relação com usuários (estudantes)
     * Um universidade tem muitos estudantes
     */
    public function students()
    {
        return $this->hasMany(User::class, 'university_id');
    }

    /**
     * Relação com surveys (pesquisas)
     * Uma universidade pode ter muitas pesquisas
     */
    public function surveys()
    {
        return $this->hasMany(Survey::class, 'institution', 'name');
    }

    /**
     * MÉTODOS ÚTEIS
     */

    /**
     * Obter nome formatado com sigla
     */
    public function getFullNameAttribute()
    {
        if ($this->acronym) {
            return "{$this->name} ({$this->acronym})";
        }
        return $this->name;
    }

    /**
     * Verificar se é universidade pública
     */
    public function isPublic()
    {
        return $this->type === 'public' || stripos($this->type, 'pública') !== false;
    }

    /**
     * Verificar se é universidade privada
     */
    public function isPrivate()
    {
        return $this->type === 'private' || stripos($this->type, 'privada') !== false;
    }

    /**
     * Obter URL do website formatada
     */
    public function getWebsiteUrlAttribute()
    {
        if (!$this->website) {
            return null;
        }

        // Adicionar https:// se não tiver
        if (!preg_match('/^https?:\/\//', $this->website)) {
            return 'https://' . $this->website;
        }

        return $this->website;
    }

    /**
     * Contar número de estudantes ativos
     */
    public function countActiveStudents()
    {
        return $this->students()
            ->where('role', 'student')
            ->where('verification_status', 'approved')
            ->count();
    }

    /**
     * ESCOPOS DE CONSULTA
     */

    /**
     * Escopo para universidades públicas
     */
    public function scopePublic($query)
    {
        return $query->where('type', 'public')
            ->orWhere('type', 'like', '%pública%');
    }

    /**
     * Escopo para universidades privadas
     */
    public function scopePrivate($query)
    {
        return $query->where('type', 'private')
            ->orWhere('type', 'like', '%privada%');
    }

    /**
     * Escopo para universidades em determinada localização
     */
    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    /**
     * Escopo para universidades ordenadas por nome
     */
    public function scopeOrderByName($query, $direction = 'asc')
    {
        return $query->orderBy('name', $direction);
    }

    /**
     * Escopo para universidades ordenadas por ordem
     */
    public function scopeOrderByOrder($query, $direction = 'asc')
    {
        return $query->orderBy('order', $direction);
    }

    /**
     * Escopo para universidades com website
     */
    public function scopeWithWebsite($query)
    {
        return $query->whereNotNull('website')
            ->where('website', '!=', '');
    }

    /**
     * VALIDAÇÕES
     */

    /**
     * Validar dados da universidade
     */
    public static function validate(array $data)
    {
        return validator($data, [
            'name' => 'required|string|max:191',
            'acronym' => 'nullable|string|max:191',
            'type' => 'required|string|max:191',
            'location' => 'nullable|string|max:191',
            'website' => 'nullable|url|max:191',
            'order' => 'nullable|integer|min:0',
        ]);
    }

    /**
     * MÉTODOS ESTÁTICOS
     */

    /**
     * Buscar universidade pelo nome ou sigla
     */
    public static function findByNameOrAcronym($search)
    {
        return self::where('name', 'like', "%{$search}%")
            ->orWhere('acronym', 'like', "%{$search}%")
            ->first();
    }

    /**
     * Obter todas as localizações únicas
     */
    public static function getUniqueLocations()
    {
        return self::select('location')
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->orderBy('location')
            ->pluck('location');
    }

    /**
     * Obter estatísticas de universidades
     */
    public static function getStats()
    {
        return [
            'total' => self::count(),
            'public' => self::public()->count(),
            'private' => self::private()->count(),
            'with_website' => self::withWebsite()->count(),
            'locations' => self::getUniqueLocations()->count(),
        ];
    }

    /**
     * EVENTOS
     */

    protected static function booted()
    {
        // Antes de salvar, garantir que a ordem seja um número
        static::saving(function ($university) {
            if (empty($university->order)) {
                $university->order = 0;
            }
        });

        // Depois de salvar, atualizar a ordem se necessário
        static::saved(function ($university) {
            // Lógica para reordenar se necessário
        });
    }
}
