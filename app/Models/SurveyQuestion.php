<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyQuestion extends Model
{
    protected $table = 'survey_questions';

    protected $fillable = [
        'survey_id',
        'title',
        'question',
        'description',
        'type',
        'options',
        'placeholder',
        'default_value',
        'min_value',
        'max_value',
        'low_label',
        'high_label',
        'required',
        'order',
        'image_url',
        'validation_rules',
        'metadata',
        'min_length',
        'max_length',
        'scale_min',
        'scale_max',
        'scale_step',
        'scale_low_label',
        'scale_high_label',
        'scale_value',
        'min_date',
        'max_date',
        'min_time',
        'max_time',
        'hint',
    ];

    protected $casts = [
        'options' => 'array',           // ✅ CRÍTICO: Converte JSON para array
        'validation_rules' => 'array',
        'metadata' => 'array',
        'required' => 'boolean',
        'min_value' => 'integer',
        'max_value' => 'integer',
        'order' => 'integer',
        'min_length' => 'integer',
        'max_length' => 'integer',
        'scale_min' => 'integer',
        'scale_max' => 'integer',
        'scale_step' => 'integer',
        'scale_value' => 'integer',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * ✅ Obter options como array (seguro)
     */
    public function getOptionsArray(): array
    {
        // Com o cast 'options' => 'array', isso já retorna array
        return $this->options ?? [];
    }

    /**
     * ✅ Obter options formatadas para o frontend
     */
    public function getFormattedOptions(): array
    {
        $options = $this->getOptionsArray();
        $formatted = [];

        foreach ($options as $key => $opt) {
            // Se for array associativo com value/label
            if (is_array($opt)) {
                $formatted[] = [
                    'value' => $opt['value'] ?? $key,
                    'label' => $opt['label'] ?? $opt['value'] ?? $key,
                    'description' => $opt['description'] ?? null,
                ];
            }
            // Se for array simples (valor como string)
            elseif (is_numeric($key)) {
                $formatted[] = [
                    'value' => $opt,
                    'label' => $opt,
                    'description' => null,
                ];
            }
            // Se for objeto (key => label)
            else {
                $formatted[] = [
                    'value' => $key,
                    'label' => $opt,
                    'description' => null,
                ];
            }
        }

        return $formatted;
    }

    /**
     * ✅ Verificar se tem opções
     */
    public function hasOptions(): bool
    {
        return in_array($this->type, [
            'multiple_choice',
            'checkboxes',
            'dropdown',
            'radio',
            'select'
        ]) && !empty($this->getOptionsArray());
    }

    /**
     * ✅ Obter número de opções
     */
    public function getOptionsCount(): int
    {
        return count($this->getOptionsArray());
    }

    /**
     * ✅ Obter regras de validação
     */
    public function getValidationRules(): array
    {
        $rules = [];

        if ($this->required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        switch ($this->type) {
            case 'text':
            case 'short_text':
                $rules[] = 'string';
                $rules[] = 'max:' . ($this->max_length ?? 255);
                if ($this->min_length) {
                    $rules[] = 'min:' . $this->min_length;
                }
                break;

            case 'paragraph':
            case 'long_text':
                $rules[] = 'string';
                $rules[] = 'max:' . ($this->max_length ?? 5000);
                if ($this->min_length) {
                    $rules[] = 'min:' . $this->min_length;
                }
                break;

            case 'multiple_choice':
            case 'dropdown':
            case 'radio':
            case 'select':
                $rules[] = 'string';
                if ($this->hasOptions()) {
                    $options = array_column($this->getFormattedOptions(), 'value');
                    $rules[] = 'in:' . implode(',', $options);
                }
                break;

            case 'checkboxes':
                $rules[] = 'array';
                if ($this->hasOptions()) {
                    $options = array_column($this->getFormattedOptions(), 'value');
                    $rules[] = 'in:' . implode(',', $options);
                }
                break;

            case 'linear_scale':
            case 'scale':
                $rules[] = 'integer';
                if ($this->scale_min !== null) {
                    $rules[] = 'min:' . $this->scale_min;
                }
                if ($this->scale_max !== null) {
                    $rules[] = 'max:' . $this->scale_max;
                }
                break;

            case 'rating':
                $rules[] = 'integer';
                $rules[] = 'min:1';
                $rules[] = 'max:5';
                break;

            case 'yes_no':
                $rules[] = 'in:sim,nao,1,0,true,false';
                break;

            case 'date':
                $rules[] = 'date';
                if ($this->min_date) {
                    $rules[] = 'after_or_equal:' . $this->min_date;
                }
                if ($this->max_date) {
                    $rules[] = 'before_or_equal:' . $this->max_date;
                }
                break;

            case 'time':
                $rules[] = 'date_format:H:i';
                if ($this->min_time) {
                    $rules[] = 'after_or_equal:' . $this->min_time;
                }
                if ($this->max_time) {
                    $rules[] = 'before_or_equal:' . $this->max_time;
                }
                break;

            case 'number':
                $rules[] = 'numeric';
                if ($this->min_value !== null) {
                    $rules[] = 'min:' . $this->min_value;
                }
                if ($this->max_value !== null) {
                    $rules[] = 'max:' . $this->max_value;
                }
                break;
        }

        // Adicionar regras personalizadas
        if ($this->validation_rules && is_array($this->validation_rules)) {
            foreach ($this->validation_rules as $rule) {
                $rules[] = $rule;
            }
        }

        return array_unique($rules);
    }

    /**
     * ✅ Obter título ou fallback
     */
    public function getDisplayTitle(): string
    {
        return $this->title ?? $this->question ?? 'Pergunta sem título';
    }

    /**
     * ✅ Obter descrição
     */
    public function getDescription(): ?string
    {
        return $this->description ?? null;
    }

    /**
     * ✅ Obter hint/dica
     */
    public function getHint(): ?string
    {
        return $this->hint ?? null;
    }

    /**
     * ✅ Verificar tipo de pergunta
     */
    public function isTextType(): bool
    {
        return in_array($this->type, ['text', 'short_text', 'paragraph', 'long_text']);
    }

    public function isChoiceType(): bool
    {
        return in_array($this->type, ['multiple_choice', 'checkboxes', 'dropdown', 'radio', 'select']);
    }

    public function isScaleType(): bool
    {
        return in_array($this->type, ['linear_scale', 'scale', 'rating']);
    }

    public function isDateTimeType(): bool
    {
        return in_array($this->type, ['date', 'time']);
    }
}
