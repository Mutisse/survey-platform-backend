<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnifiedConfigController extends Controller
{
    /**
     * Obter todas as configurações do sistema (SEM FALLBACK)
     */
    public function getAllConfigs()
    {
        try {
            // Buscar configurações da nova estrutura
            $configurations = DB::table('config_values as cv')
                ->join('config_types as ct', 'cv.tipo_id', '=', 'ct.id')
                ->where('cv.is_active', true)
                ->orderBy('ct.nome_tipo')
                ->orderBy('cv.valor')
                ->get()
                ->groupBy('nome_tipo');

            // Dados da tabela universities
            $universities = DB::table('universities')
                ->whereNotNull('name')
                ->orderBy('order')
                ->get(['id', 'name', 'acronym', 'type', 'location', 'website']);

            return response()->json([
                'success' => true,
                'data' => [
                    'provinces' => $this->formatSimpleOptions($configurations->get('province')),
                    'occupations' => $this->formatSimpleOptions($configurations->get('occupations')),
                    'education_levels' => $this->formatSimpleOptions($configurations->get('education_levels')),
                    'participation_frequencies' => $this->formatSimpleOptions($configurations->get('participation_frequencies')),
                    'institution_types' => $this->formatSimpleOptions($configurations->get('institution_types')),
                    'courses' => $this->formatSimpleOptions($configurations->get('courses')),
                    'academic_levels' => $this->formatSimpleOptions($configurations->get('academic_levels')),
                    'research_areas' => $this->formatLabelValueOptions($configurations->get('research_areas')),
                    'universities' => $universities
                ],
                'message' => 'Configurações carregadas com sucesso',
                'timestamp' => now()->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar configurações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter configurações para participantes (SEM FALLBACK)
     */
    public function getParticipantConfigs()
    {
        try {
            $configurations = DB::table('config_values as cv')
                ->join('config_types as ct', 'cv.tipo_id', '=', 'ct.id')
                ->where('cv.is_active', true)
                ->orderBy('ct.nome_tipo')
                ->orderBy('cv.valor')
                ->get()
                ->groupBy('nome_tipo');

            return response()->json([
                'success' => true,
                'data' => [
                    'provinces' => $this->formatSimpleOptions($configurations->get('province')),
                    'occupations' => $this->formatSimpleOptions($configurations->get('occupations')),
                    'education_levels' => $this->formatSimpleOptions($configurations->get('education_levels')),
                    'research_areas' => $this->formatLabelValueOptions($configurations->get('research_areas')),
                    'participation_frequencies' => $this->formatSimpleOptions($configurations->get('participation_frequencies')),
                ],
                'message' => 'Configurações para participantes obtidas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter configurações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter configurações para estudantes (SEM FALLBACK)
     */
    public function getStudentConfigs()
    {
        try {
            $configurations = DB::table('config_values as cv')
                ->join('config_types as ct', 'cv.tipo_id', '=', 'ct.id')
                ->where('cv.is_active', true)
                ->orderBy('ct.nome_tipo')
                ->orderBy('cv.valor')
                ->get()
                ->groupBy('nome_tipo');

            $universities = DB::table('universities')
                ->whereNotNull('name')
                ->orderBy('order')
                ->get(['id', 'name', 'acronym', 'type', 'location']);

            return response()->json([
                'success' => true,
                'data' => [
                    'universities' => $universities,
                    'institution_types' => $this->formatSimpleOptions($configurations->get('institution_types')),
                    'courses' => $this->formatSimpleOptions($configurations->get('courses')),
                    'academic_levels' => $this->formatSimpleOptions($configurations->get('academic_levels')),
                    'research_areas' => $this->formatLabelValueOptions($configurations->get('research_areas')),
                ],
                'message' => 'Configurações para estudantes obtidas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter configurações: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============ CRUD COMPLETO ============

    /**
     * Criar nova configuração
     */
    public function createConfig(Request $request)
    {
        try {
            $validated = $request->validate([
                'tipo' => 'required|string|exists:config_types,nome_tipo',
                'valor' => 'required|string|max:200',
                'is_active' => 'boolean'
            ]);

            // Verificar se já existe
            $tipo = DB::table('config_types')
                ->where('nome_tipo', $validated['tipo'])
                ->first();

            $exists = DB::table('config_values')
                ->where('tipo_id', $tipo->id)
                ->where('valor', $validated['valor'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta configuração já existe'
                ], 409);
            }

            // Inserir valor
            $id = DB::table('config_values')->insertGetId([
                'tipo_id' => $tipo->id,
                'valor' => $validated['valor'],
                'is_active' => $validated['is_active'] ?? true,
                'created_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'data' => ['id' => $id],
                'message' => 'Configuração criada com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todas configurações de um tipo
     */
    public function listConfigs($tipo)
    {
        try {
            $configs = DB::table('config_values as cv')
                ->join('config_types as ct', 'cv.tipo_id', '=', 'ct.id')
                ->where('ct.nome_tipo', $tipo)
                ->where('cv.is_active', true)
                ->orderBy('cv.valor')
                ->get(['cv.id', 'cv.valor', 'cv.is_active', 'cv.created_at']);

            return response()->json([
                'success' => true,
                'data' => $configs,
                'message' => "Configurações do tipo '{$tipo}' listadas"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar configuração por ID
     */
    public function getConfig($id)
    {
        try {
            $config = DB::table('config_values as cv')
                ->join('config_types as ct', 'cv.tipo_id', '=', 'ct.id')
                ->where('cv.id', $id)
                ->first(['cv.id', 'ct.nome_tipo as tipo', 'cv.valor', 'cv.is_active', 'cv.created_at']);

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuração não encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar configuração
     */
    public function updateConfig(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'valor' => 'sometimes|string|max:200',
                'is_active' => 'sometimes|boolean'
            ]);

            $updateData = [];
            if (isset($validated['valor'])) {
                $updateData['valor'] = $validated['valor'];
            }
            if (isset($validated['is_active'])) {
                $updateData['is_active'] = $validated['is_active'];
            }
            $updateData['updated_at'] = now();

            $updated = DB::table('config_values')
                ->where('id', $id)
                ->update($updateData);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Configuração atualizada com sucesso'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Configuração não encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar configuração
     */
    public function deleteConfig($id)
    {
        try {
            $deleted = DB::table('config_values')
                ->where('id', $id)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Configuração deletada com sucesso'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Configuração não encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============ MÉTODOS AUXILIARES DE FORMATAÇÃO ============

    private function formatSimpleOptions($collection)
    {
        if (!$collection || $collection->isEmpty()) {
            return [];
        }
        return $collection->pluck('valor')->values()->toArray();
    }

    private function formatLabelValueOptions($collection)
    {
        if (!$collection || $collection->isEmpty()) {
            return [];
        }
        return $collection->map(function ($item) {
            return [
                'value' => $item->valor,
                'label' => $item->valor
            ];
        })->values()->toArray();
    }
}
