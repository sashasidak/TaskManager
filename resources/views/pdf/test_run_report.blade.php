<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img.logo {
            width: 300px;
            height: auto;
        }
        .header img {
            width: 150px;
            height: auto;
        }
        .content {
            margin: 20px;
        }
        .chart {
            text-align: center;
            margin-top: 20px;
        }
        .chart img {
            max-width: 100%;
            height: auto;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .table th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="{{ public_path('img/Logo Abank@3x.png') }}" alt="Logo" class="logo">
        </div>
        <h1>{{ $reportTitle }}</h1>
        <p>{{ $testRun->title  }}</p>
    </div>
    <div class="content">
        <div class="chart">
            <img src="{{ $chartImagePath }}" alt="Диаграмма">
        </div>
        <div class="summary">
            <h2>Общая информация</h2>
            <p>Количество модулей/задач: {{ $taskCount }}</p>
            <p>Общее количество тест-кейсов: {{ $totalTestCasesCount }}</p>
        </div>
        <h2>Статистика по тест-кейсам</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Статус</th>
                    <th>Количество</th>
                    <th>Процент</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statusCounts as $status => $count)
                    <tr>
                        <td>{{ ucfirst($status) }}</td>
                        <td>{{ $count }}</td>
                        <td>{{ $totalTestCasesCount > 0 ? round(($count / $totalTestCasesCount) * 100, 2) . '%' : '0%' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="additional-info">
            <h2>Дополнительная информация</h2>
            <p>Данные по смартфонам: {{ $smartphoneData }}</p>
            <p>Комментарий: {{ $comment }}</p>
        </div>
    </div>
</body>
</html>
