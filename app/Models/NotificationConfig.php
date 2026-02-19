<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationConfig extends Model
{
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'notification_configs';

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'title',
        'message',
        'icon',
        'action_label',
        'action_url',
        'priority',
        'expires_in_days',
        'allowed_roles',
        'is_system',
        'is_active'
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'allowed_roles' => 'array',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'priority' => 'integer',
        'expires_in_days' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Substituir variáveis na mensagem
     *
     * @param array $data
     * @return string
     */
    public function formatMessage(array $data = []): string
    {
        return $this->replaceVariables($this->message, $data);
    }

    /**
     * Substituir variáveis no título
     *
     * @param array $data
     * @return string
     */
    public function formatTitle(array $data = []): string
    {
        return $this->replaceVariables($this->title, $data);
    }

    /**
     * Substituir variáveis na URL
     *
     * @param array $data
     * @return string|null
     */
    public function formatUrl(array $data = []): ?string
    {
        if (!$this->action_url) {
            return null;
        }
        return $this->replaceVariables($this->action_url, $data);
    }

    /**
     * Substituir variáveis em um texto
     *
     * @param string $text
     * @param array $data
     * @return string
     */
    private function replaceVariables(string $text, array $data): string
    {
        foreach ($data as $key => $value) {
            $text = str_replace('{' . $key . '}', (string) $value, $text);
        }
        return $text;
    }

    /**
     * Verificar se um role tem permissão para receber esta notificação
     *
     * @param string $role
     * @return bool
     */
    public function isAllowedForRole(string $role): bool
    {
        $allowedRoles = $this->allowed_roles ?? [];

        if (empty($allowedRoles)) {
            return true; // Se não definiu roles, permite todos
        }

        return in_array($role, $allowedRoles);
    }

    /**
     * Escopo para buscar apenas tipos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Escopo para buscar por tipo
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Escopo para buscar notificações de sistema
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Escopo para buscar notificações normais (não sistema)
     */
    public function scopeNormal($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Relacionamento com as notificações enviadas
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'type', 'type');
    }
}
