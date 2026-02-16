<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AcademicDataController extends Controller
{
    /**
     * Obter todos os dados acadêmicos de uma vez (para frontend)
     */
    public function getAllAcademicData()
    {
        // Dados estáticos temporários
        $data = [
            'institution_types' => [
                'Universidade Pública',
                'Universidade Privada',
                'Instituto Superior',
                'Escola Superior',
                'Outra'
            ],

            'universities' => [
                ['id' => 1, 'name' => 'Universidade Eduardo Mondlane (UEM)', 'type' => 'Universidade Pública', 'acronym' => 'UEM'],
                ['id' => 2, 'name' => 'Universidade Pedagógica (UP)', 'type' => 'Universidade Pública', 'acronym' => 'UP'],
                ['id' => 3, 'name' => 'Universidade Lúrio (UniLúrio)', 'type' => 'Universidade Pública', 'acronym' => 'UniLúrio'],
                ['id' => 4, 'name' => 'Universidade Zambeze (UniZambeze)', 'type' => 'Universidade Pública', 'acronym' => 'UniZambeze'],
                ['id' => 5, 'name' => 'Universidade Save (UniSave)', 'type' => 'Universidade Pública', 'acronym' => 'UniSave'],
                ['id' => 6, 'name' => 'Outra', 'type' => 'Outra', 'acronym' => null],
            ],

            'courses' => [
                'Engenharia Informática',
                'Medicina',
                'Direito',
                'Economia',
                'Administração e Gestão',
                'Contabilidade',
                'Enfermagem',
                'Arquitetura',
                'Engenharia Civil',
                'Outro'
            ],

            'academic_levels' => [
                'Licenciatura - 1º ano',
                'Licenciatura - 2º ano',
                'Licenciatura - 3º ano',
                'Licenciatura - 4º ano',
                'Licenciatura - 5º ano',
                'Pós-graduação',
                'Mestrado',
                'Doutoramento'
            ],

            'research_areas' => [
                ['value' => 'ciencias_sociais', 'label' => 'Ciências Sociais'],
                ['value' => 'saude', 'label' => 'Saúde'],
                ['value' => 'tecnologia', 'label' => 'Tecnologia'],
                ['value' => 'educacao', 'label' => 'Educação'],
                ['value' => 'economia', 'label' => 'Economia'],
                ['value' => 'ambiente', 'label' => 'Meio Ambiente'],
                ['value' => 'cultura', 'label' => 'Cultura'],
                ['value' => 'politica', 'label' => 'Política'],
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Dados acadêmicos carregados com sucesso',
            'data' => $data,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Obter todas as universidades
     */
    public function getUniversities(Request $request)
    {
        $universities = [
            ['id' => 1, 'name' => 'Universidade Eduardo Mondlane (UEM)', 'type' => 'Universidade Pública'],
            ['id' => 2, 'name' => 'Universidade Pedagógica (UP)', 'type' => 'Universidade Pública'],
            ['id' => 3, 'name' => 'Outra', 'type' => 'Outra'],
        ];

        return response()->json([
            'success' => true,
            'data' => $universities,
            'count' => count($universities)
        ]);
    }

    /**
     * Obter tipos de instituição
     */
    public function getInstitutionTypes()
    {
        $types = [
            'Universidade Pública',
            'Universidade Privada',
            'Instituto Superior',
            'Escola Superior',
            'Outra'
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'institution_types' => $types,
                'options' => array_map(function($type) {
                    return ['value' => $type, 'label' => $type];
                }, $types)
            ]
        ]);
    }

    /**
     * Obter cursos
     */
    public function getCourses(Request $request)
    {
        $courses = [
            'Engenharia Informática',
            'Medicina',
            'Direito',
            'Economia',
            'Outro'
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'courses' => $courses,
                'options' => array_map(function($course) {
                    return ['value' => $course, 'label' => $course];
                }, $courses)
            ]
        ]);
    }

    /**
     * Obter níveis acadêmicos
     */
    public function getAcademicLevels()
    {
        $levels = [
            'Licenciatura - 1º ano',
            'Licenciatura - 2º ano',
            'Licenciatura - 3º ano',
            'Licenciatura - 4º ano',
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'academic_levels' => $levels,
                'options' => array_map(function($level) {
                    return ['value' => $level, 'label' => $level];
                }, $levels)
            ]
        ]);
    }

    /**
     * Obter áreas de pesquisa
     */
    public function getResearchAreas()
    {
        $areas = [
            ['value' => 'tecnologia', 'label' => 'Tecnologia'],
            ['value' => 'saude', 'label' => 'Saúde'],
            ['value' => 'educacao', 'label' => 'Educação'],
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'research_areas' => $areas,
                'options' => $areas
            ]
        ]);
    }
}
