<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Test Run Report</title>
    <style>
        body {
            background-color: #f5f5f5; /* Фон страницы */
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            padding: 20px;
            background: linear-gradient(to bottom, #ffffff, #e3f2fd); /* Градиент от белого к светло-синему */
            border: 1px solid #ddd; /* Легкая рамка вокруг */
            border-radius: 8px; /* Скругление углов рамки */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Тень вокруг блока */
        }

        h1, h3 {
            color: #333;
            font-style: italic;
            text-align: center;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 28px;
            font-weight: bold;
        }

        .section {
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .section h3 {
            margin: 0 0 10px;
            font-size: 20px;
            font-weight: bold;
            color: #007bff; /* Цвет заголовка */
        }

        .section p {
            margin: 0;
            font-size: 16px;
            line-height: 1.6;
            color: #333;
        }

        .suite-title {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
            font-style: italic;
        }

        .phone-section, .comment-section {
            text-align: left;
        }

        .phone-section h3, .comment-section h3 {
            color: #007bff; /* Цвет заголовка */
        }

        .chart-section {
            text-align: center;
            margin-bottom: 20px;
        }

        .chart-section img {
            max-width: 100%;
            height: auto;
        }

        .test-case-container {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .test-case-container th, .test-case-container td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        .test-case-container th {
            background-color: #f4f4f4;
            color: #333;
            text-align: center;
            font-size: 14px;
            border-bottom: 2px solid #ddd;
        }

        .test-case-container td {
            background-color: #fff;
            vertical-align: middle;
        }

        .test-case-title {
            text-align: left;
            font-weight: bold;
            font-size: 16px;
        }

        .test-case-status {
            text-align: center;
        }

        .status-label {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            color: #fff;
        }

        .status-passed {
            background-color: #28a745;
        }

        .status-failed {
            background-color: #dc3545;
        }

        .status-blocked {
            background-color: #ffc107;
            color: #333;
        }

        .status-not-tested {
            background-color: #6c757d;
        }

        .test-case-container tr:nth-child(even) td {
            background-color: #f9f9f9;
        }

        .test-case-container tr:hover td {
            background-color: #e9ecef;
        }

        .divider-row {
            background-color: #f4f4f4;
            font-weight: bold;
            text-align: center;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 300px;
            height: auto;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="logo">
        <img src="{{ public_path('img/Logo Abank@3x.png') }}" alt="Logo">
    </div>

    <h1>Отчет о тестировании: {{ $project->name }}</h1>

    @if($relatedSuites->count())
        <div class="suite-title">
            <h3>{{ $relatedSuites->first()->title }}</h3>
        </div>
    @endif

    @if($phoneFieldData)
        <div class="section phone-section">
            <h3>Тестовое окружение:</h3>
            <p>{{ $phoneFieldData }}</p>
        </div>
    @endif

    @if($comment)
        <div class="section comment-section">
            <h3>Комментарий:</h3>
            <p>{{ $comment }}</p>
        </div>
    @endif

    @if($chartImagePath)
        <div class="chart-section">
            <h3>Диаграмма статусов:</h3>
            <img src="{{ $chartImagePath }}" alt="Status Chart">
        </div>
    @endif

    <table class="test-case-container">
        <thead>
        <tr>
            <th>Test Case:</th>
            <th>Status:</th>
        </tr>
        </thead>
        <tbody>
        @foreach($relatedSuites as $suite)
            <tr class="divider-row">
                <td colspan="2">{{ $suite->title }}</td>
            </tr>
            @foreach($suite->testCases as $testCase)
                @if($testCases->contains($testCase))
                    <tr>
                        <td class="test-case-title">{{ $testCase->title }}</td>
                        <td class="test-case-status">
                            @switch($results[$testCase->id] ?? 4)
                                @case(1)
                                    <span class="status-label status-passed">Passed</span>
                                    @break
                                @case(2)
                                    <span class="status-label status-failed">Failed</span>
                                    @break
                                @case(3)
                                    <span class="status-label status-blocked">Blocked</span>
                                    @break
                                @case(4)
                                    <span class="status-label status-not-tested">Not Tested</span>
                                    @break
                            @endswitch
                        </td>
                    </tr>
                @endif
            @endforeach
        @endforeach
        </tbody>
    </table>
</div>

</body>
</html>
