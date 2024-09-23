<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }}</title>
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
        .content {
            margin: 20px auto;
            max-width: 800px;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .chart {
            text-align: center;
            margin-top: 20px;
        }
        .chart img {
            max-width: 100%;
            height: auto;
        }
        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .summary h2 {
            color: #333;
            font-size: 20px;
            margin-top: 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        .table th {
            background-color: #4CAF50;
            color: white;
        }
        .table td {
            background-color: #f9f9f9;
        }
        .table tr:nth-child(even) td {
            background-color: #f2f2f2;
        }
        .additional-info {
            margin-top: 20px;
        }
        .additional-info h2 {
            color: #333;
            font-size: 20px;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        .additional-info p {
            font-size: 16px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="{{ public_path('img/Logo Abank@3x.png') }}" alt="Logo">
        </div>
        <h1>{{ $reportTitle }}</h1>
        <p>{{ $testRun->title }}</p>
        <p>{{ $description }}</p>
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
            <p>Комментарий:</p>
                    <div class="comment">
                        {!! $comment !!}
                    </div>
        </div>
    </div>
</body>
</html>
