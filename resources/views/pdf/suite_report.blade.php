<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Test Run Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        h1, h3 {
            color: #333;
            margin-bottom: 10px;
            font-style: italic; /* Курсивный стиль текста */
            text-align: center; /* Центрирование заголовков */
        }
        h1 {
            font-size: 28px; /* Размер шрифта для h1 */
            font-weight: bold; /* Жирное начертание */
        }
        .suite-title {
            text-align: center; /* Центрирование заголовков секций */
            margin-bottom: 20px; /* Отступ снизу */
            width: 100%; /* Занимает всю ширину */
            font-style: italic; /* Курсивный стиль текста */
            font-size: 24px; /* Размер шрифта */
            font-weight: bold; /* Жирное начертание */
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
            text-align: center; /* Центрирование текста заголовков */
            font-size: 14px;
            border-bottom: 2px solid #ddd;
        }
        .test-case-container td {
            background-color: #fff;
            vertical-align: middle;
        }
        .test-case-title {
            text-align: left; /* Выравнивание текста по левому краю */
            font-weight: bold;
            font-size: 16px;
        }
        .test-case-status {
            text-align: center; /* Центрирование статуса */
        }
        .status-label {
            display: inline-block;
            padding: 4px 8px; /* Уменьшены отступы */
            border-radius: 4px; /* Уменьшен радиус скругления */
            font-size: 12px; /* Уменьшен размер шрифта */
            font-weight: bold;
            color: #fff;
        }
        .status-passed {
            background-color: #28a745; /* Зеленый */
        }
        .status-failed {
            background-color: #dc3545; /* Красный */
        }
        .status-blocked {
            background-color: #ffc107; /* Желтый */
            color: #333; /* Темный текст */
        }
        .status-not-tested {
            background-color: #6c757d; /* Серый */
        }
        .test-case-container tr:nth-child(even) td {
            background-color: #f9f9f9; /* Светло-серый фон для четных строк */
        }
        .test-case-container tr:hover td {
            background-color: #e9ecef; /* Цвет фона при наведении */
        }
    </style>
</head>
<body>

<h1>Отчет о тестировании: {{ $project->name }}</h1>

@foreach($suites as $suite)
    <div class="suite-title">
        <h3>{{ $suite->title }}</h3>
    </div>

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
        </tbody>
    </table>
@endforeach

</body>
</html>
