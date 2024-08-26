<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Test Run Report</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            padding: 20px;
            background: #ffffff;
            border-bottom: 1px solid #e0e0e0;
        }

        .header .logo img {
            width: 200px;
            height: auto;
        }

        .header h1 {
            color: #333;
            font-size: 24px;
            margin: 10px 0;
        }

        .header p {
            color: #777;
            font-size: 16px;
        }

        .container {
            margin: 20px auto;
            max-width: 800px;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .section {
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .section h3 {
            color: #333;
            font-size: 20px;
            margin-top: 0;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }

        .section p {
            font-size: 16px;
            color: #555;
        }

        .chart-section {
            text-align: center;
            margin-top: 20px;
        }

        .chart-section img {
            max-width: 100%;
            height: auto;
        }

        .test-case-container {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .test-case-container th, .test-case-container td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        .test-case-container th {
            background-color: #4CAF50;
            color: white;
        }

        .test-case-container td {
            background-color: #f9f9f9;
        }

        .test-case-container tr:nth-child(even) td {
            background-color: #f2f2f2;
        }

        .test-case-container tr:hover td {
            background-color: #e9ecef;
        }

        .test-case-title {
            text-align: left;
            font-weight: bold;
        }

        .test-case-status {
            text-align: center;
        }

        .status-label {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
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
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="{{ public_path('img/Logo Abank@3x.png') }}" alt="Logo">
        </div>
        <h1>Отчет о тестировании:</h1>
        <p>{{ $suite->title }}</p>
    </div>

    <div class="container">
        @if($phoneFieldData)
            <div class="section">
                <h3>Тестовое окружение:</h3>
                <p>{{ $phoneFieldData }}</p>
            </div>
        @endif

        @if($comment)
            <div class="section">
                <h3>Комментарий:</h3>
                    <div class="comment">
                       {!! $comment !!}
                    </div>
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
                    <th>Test Case</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($relatedSuites as $suite)
                    <tr>
                        <td colspan="2" style="font-weight: bold; background-color: #f4f4f4;">{{ $suite->title }}</td>
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
