<!DOCTYPE html>
<html>
<head>
    <title>{{ $reportTitle }}</title>
    <style>
        /* Добавьте стили для отчета здесь */
        body { font-family: Arial, sans-serif; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <h1>{{ $reportTitle }}</h1>
    <h3>Test Run: {{ $testRun->title }}</h3>
    <p><strong>Smartphone Data:</strong> {{ $smartphoneData }}</p>
    <p><strong>Comment:</strong> {{ $comment }}</p>
    <p><strong>Total Tasks in Test Plan:</strong> {{ $taskCount }}</p> <!-- Количество основных задач -->
    <p><strong>Total Test Cases in Test Plan:</strong> {{ $totalTestCasesCount }}</p> <!-- Общее количество тест-кейсов -->

    <h3>Status Counts:</h3>
    <ul>
        <li>Passed: {{ $statusCounts['passed'] }}</li>
        <li>Failed: {{ $statusCounts['failed'] }}</li>
        <li>Blocked: {{ $statusCounts['blocked'] }}</li>
        <li>Not Tested: {{ $statusCounts['not_tested'] }}</li>
    </ul>

    <!-- Добавьте другие части отчета, если необходимо -->
</body>
</html>
