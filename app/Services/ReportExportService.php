<?php

namespace App\Services;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ReportExportService
{
    /**
     * Exportar para CSV
     */
    public function exportToCsv(array $data, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Adicionar BOM para UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            if (isset($data['headers'])) {
                fputcsv($file, $data['headers'], ';');
            }

            if (isset($data['rows'])) {
                foreach ($data['rows'] as $row) {
                    fputcsv($file, $row, ';');
                }
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Exportar para JSON
     */
    public function exportToJson(array $data, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.json"',
        ];

        return Response::stream(function () use ($json) {
            echo $json;
        }, 200, $headers);
    }

    /**
     * Exportar para Excel (XLSX usando PHP nativo com PHPOffice)
     * Se não tiver PHPOffice, cria CSV com extensão .xlsx
     */
    public function exportToExcel(array $data, string $filename, array $sheets = []): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        // Se tiver PHPOffice instalado, usa ele
        if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            return $this->exportWithPhpSpreadsheet($data, $filename, $sheets);
        }

        // Caso contrário, cria um CSV com extensão .xlsx
        return $this->exportToCsv($data, $filename);
    }

    /**
     * Exportar para TXT
     */
    public function exportToTxt(array $data, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $content = '';

        if (isset($data['headers'])) {
            $content .= implode("\t", $data['headers']) . "\n";
        }

        if (isset($data['rows'])) {
            foreach ($data['rows'] as $row) {
                $content .= implode("\t", $row) . "\n";
            }
        }

        $headers = [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.txt"',
        ];

        return Response::stream(function () use ($content) {
            echo $content;
        }, 200, $headers);
    }

    /**
     * Formatador de dados para relatórios
     */
    public function formatReportData(array $reportData, string $format = 'table'): array
    {
        switch ($format) {
            case 'csv':
            case 'excel':
                return $this->formatForCsv($reportData);
            case 'json':
                return $reportData;
            case 'pdf':
                return $this->formatForPdf($reportData);
            default:
                return $reportData;
        }
    }

    /**
     * Formatar dados para CSV/Excel
     */
    private function formatForCsv(array $data): array
    {
        $formatted = [];

        if (isset($data['summary'])) {
            $formatted['summary'] = [
                'headers' => ['Métrica', 'Valor', 'Variação', 'Período'],
                'rows' => array_map(function ($key, $value) {
                    return [
                        is_array($value) ? ($value['label'] ?? $key) : $key,
                        is_array($value) ? ($value['value'] ?? $value) : $value,
                        is_array($value) ? ($value['trend'] ?? 'N/A') : 'N/A',
                        is_array($value) ? ($value['period'] ?? 'N/A') : 'N/A'
                    ];
                }, array_keys($data['summary']), array_values($data['summary']))
            ];
        }

        if (isset($data['users'])) {
            $formatted['users'] = [
                'headers' => ['Nome', 'Email', 'Tipo', 'Status', 'Cadastrado em', 'Último Acesso'],
                'rows' => $data['users']
            ];
        }

        return $formatted;
    }

    /**
     * Formatar dados para PDF
     */
    private function formatForPdf(array $data): array
    {
        // Adiciona metadados para PDF
        return array_merge($data, [
            'generated_at' => now()->format('d/m/Y H:i:s'),
            'page_title' => 'Relatório do Sistema',
            'logo_url' => null
        ]);
    }

    /**
     * Usar PhpSpreadsheet se disponível
     */
    private function exportWithPhpSpreadsheet(array $data, string $filename, array $sheets): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // Remove sheet padrão

        $sheetIndex = 0;
        foreach ($sheets as $sheetName) {
            if (isset($data[$sheetName])) {
                $worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $sheetName);
                $spreadsheet->addSheet($worksheet, $sheetIndex);

                $sheetData = $data[$sheetName];

                // Adicionar cabeçalhos
                if (isset($sheetData['headers'])) {
                    $col = 'A';
                    foreach ($sheetData['headers'] as $header) {
                        $worksheet->setCellValue($col . '1', $header);
                        $col++;
                    }
                }

                // Adicionar dados
                if (isset($sheetData['rows'])) {
                    $row = 2;
                    foreach ($sheetData['rows'] as $rowData) {
                        $col = 'A';
                        foreach ($rowData as $cell) {
                            $worksheet->setCellValue($col . $row, $cell);
                            $col++;
                        }
                        $row++;
                    }
                }

                $sheetIndex++;
            }
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.xlsx"',
        ];

        return Response::stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, $headers);
    }
}
