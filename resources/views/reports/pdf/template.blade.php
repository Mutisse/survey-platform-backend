<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            color: #333;
            margin: 0;
            font-size: 18px;
        }

        .header .subtitle {
            color: #666;
            font-size: 11px;
            margin-top: 5px;
        }

        .summary {
            margin: 15px 0;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .summary-item {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
        }

        .summary-label {
            font-size: 10px;
            color: #666;
            margin-bottom: 3px;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        .summary-trend {
            font-size: 10px;
        }

        .trend-positive {
            color: #4CAF50;
        }

        .trend-negative {
            color: #F44336;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10px;
        }

        .table th {
            background: #f5f5f5;
            padding: 6px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .table td {
            padding: 6px;
            border: 1px solid #ddd;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .insight-item {
            margin: 8px 0;
            padding: 8px;
            background: #f8f9fa;
            border-left: 3px solid #007bff;
        }

        .insight-title {
            font-weight: bold;
            margin-bottom: 3px;
        }

        .insight-desc {
            font-size: 10px;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">
            Gerado por: {{ $user->name }}<br>
            Data de geração: {{ $generated_at }}<br>
            {{ config('app.name') }}
        </div>
    </div>

    @if($include_data && isset($data['summary']))
    <div class="section-title">Resumo</div>
    <div class="summary-grid">
        @foreach($data['summary'] as $key => $item)
        <div class="summary-item">
            <div class="summary-label">{{ is_array($item) ? ($item['label'] ?? $key) : $key }}</div>
            <div class="summary-value">{{ is_array($item) ? ($item['value'] ?? $item) : $item }}</div>
            @if(is_array($item) && isset($item['trend']))
            <div class="summary-trend {{ isset($item['trend_class']) ? $item['trend_class'] : '' }}">
                {{ $item['trend'] }}
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    @if($include_data && isset($data['tables']))
    @foreach($data['tables'] as $tableName => $table)
    @if(!$loop->first)<div class="page-break"></div>@endif
    <div class="section-title">{{ $table['title'] ?? ucfirst($tableName) }}</div>
    @if(isset($table['headers']) && isset($table['rows']))
    <table class="table">
        <thead>
            <tr>
                @foreach($table['headers'] as $header)
                <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($table['rows'] as $row)
            <tr>
                @foreach($row as $cell)
                <td>{{ $cell }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <table class="table">
        <tbody>
            @foreach($table as $key => $value)
            <tr>
                <td><strong>{{ $key }}</strong></td>
                <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    @endforeach
    @endif

    @if($include_insights && isset($data['insights']))
    <div class="page-break"></div>
    <div class="section-title">Insights e Recomendações</div>
    @foreach($data['insights'] as $insight)
    <div class="insight-item">
        <div class="insight-title">{{ $insight['title'] ?? 'Insight' }}</div>
        <div class="insight-desc">{{ $insight['description'] ?? '' }}</div>
        @if(isset($insight['action']))
        <div style="font-size: 9px; color: #007bff; margin-top: 3px;">
            <strong>Ação:</strong> {{ $insight['action'] }}
        </div>
        @endif
    </div>
    @endforeach
    @endif

    <div class="footer">
        <p>Sistema de Relatórios - {{ config('app.name') }}</p>
        <p>Documento gerado automaticamente. Para mais informações, acesse o sistema.</p>
    </div>
</body>

</html>
