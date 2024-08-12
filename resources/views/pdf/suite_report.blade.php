<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Test Run Report</title>
    <style>
        h1, h3 {
            color: #333;
            margin-bottom: 10px;
            font-style: italic;
            text-align: center;
        }
        h1 {
            font-size: 28px;
            font-weight: bold;
        }
        .suite-title {
            text-align: center;
            margin-bottom: 20px;
            width: 100%;
            font-style: italic;
            font-size: 24px;
            font-weight: bold;
        }
        .comment-section {
            margin-bottom: 20px;
            text-align: center;
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
            max-width: 2000px; /* Установите ширину логотипа */
            height: auto;
        }
    </style>
</head>
<body>

<div class="logo">
    <img src="{{ public_path('img/Logo Abank@3x.png') }}" alt="Logo">
</div>

<h1>Отчет о тестировании: {{ $project->name }}</h1>

@if($relatedSuites->count())
    <div class="suite-title">
        <h3>{{ $relatedSuites->first()->title }}</h3>
    </div>
@endif

@if($comment)
    <div class="comment-section">
        <h3>Комментарий:</h3>
        <p>{{ $comment }}</p>
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

</body>
</html>
