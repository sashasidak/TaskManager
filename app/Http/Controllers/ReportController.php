<?php

namespace App\Http\Controllers;

use App\Project;
use App\Repository;
use App\Suite;
use App\TestPlan;
use App\TestRun;
use App\TestCase;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function generateReport(Request $request, $projectId, $testRunId)
    {
        // Получение данных из формы
        $reportType = $request->input('reportType');
        $smartphoneData = $request->input('smartphoneData');
        $comment = $request->input('comment');

        // Выбор заголовка отчета в зависимости от типа отчета
        switch ($reportType) {
            case 'regress':
                $reportTitle = 'Отчет о регресс тестировании';
                break;
            case 'smoke':
                $reportTitle = 'Отчет о смоук тестировании';
                break;
            default:
                $reportTitle = 'Отчет о тестировании';
                break;
        }

        // Найти проект, тестовый запуск и тестовый план
        $project = Project::findOrFail($projectId);
        $testRun = TestRun::findOrFail($testRunId);
        $testPlan = TestPlan::findOrFail($testRun->test_plan_id);

        // Найти репозиторий по идентификатору из тестового плана
        $repository = Repository::findOrFail($testPlan->repository_id);

        // Извлечь идентификаторы тест-кейсов из данных тестового плана
        $testCasesIds = explode(',', $testPlan->data);

        // Найти все задачи (suites), связанные с тест-кейсами
        $suiteIds = TestCase::whereIn('id', $testCasesIds)->pluck('suite_id')->unique();
        $suites = Suite::whereIn('id', $suiteIds)->get();

        // Подсчитать количество основных задач (suites), игнорируя подзадачи
        $mainSuiteIds = Suite::whereIn('id', $suiteIds)->whereNull('parent_id')->pluck('id');
        $taskCount = $mainSuiteIds->count();

        // Подсчитать количество тест-кейсов по статусам
        $results = $testRun->getResults();
        $statusCounts = [
            'passed' => 0,
            'failed' => 0,
            'blocked' => 0,
            'not_tested' => 0,
        ];

        foreach ($testCasesIds as $testCaseId) {
            $status = $results[$testCaseId] ?? 4;
            switch ($status) {
                case 1:
                    $statusCounts['passed']++;
                    break;
                case 2:
                    $statusCounts['failed']++;
                    break;
                case 3:
                    $statusCounts['blocked']++;
                    break;
                case 4:
                    $statusCounts['not_tested']++;
                    break;
            }
        }

        // Подсчитать общее количество тест-кейсов в плане
        $totalTestCasesCount = count($testCasesIds);

        // Генерация диаграммы
        $chartImagePath = $this->generateChart($statusCounts);

        // Данные для PDF
        $data = [
            'project' => $project,
            'testRun' => $testRun,
            'testPlan' => $testPlan,
            'repository' => $repository,
            'taskCount' => $taskCount, // Количество основных задач
            'totalTestCasesCount' => $totalTestCasesCount, // Общее количество тест-кейсов
            'statusCounts' => $statusCounts,
            'reportTitle' => $reportTitle,
            'smartphoneData' => $smartphoneData, // Данные смартфона
            'comment' => $comment, // Комментарий
            'chartImagePath' => $chartImagePath, // Путь к диаграмме
        ];

        // Генерация PDF
        $pdf = SnappyPdf::loadView('pdf.test_run_report', $data);
        $pdf->setOption('enable-local-file-access', true);

        return $pdf->download("TestRun_Report_{$testRun->id}.pdf");
    }

    private function generateChart($statusCounts)
    {
        $total = array_sum($statusCounts);
        $width = 800; // Ширина диаграммы
        $height = 300; // Высота диаграммы
        $barWidth = 80; // Ширина одного столбика
        $padding = 15;   // Промежуток между столбиками
        $maxBarHeight = $height - 80; // Максимальная высота столбика (с учетом отступов)

        // Цвета с градиентами
        $colors = [
            'passed' => ['#28a745', '#218838'], // Зелёный градиент
            'failed' => ['#dc3545', '#c82333'], // Красный градиент
            'blocked' => ['#ffc107', '#e0a800'], // Жёлтый градиент
            'not_tested' => ['#6c757d', '#5a6268'] // Серый градиент
        ];

        // Начало SVG
        $svg = '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">';

        // Градиенты для заливки рамки и столбиков
        $svg .= '<defs>';
        foreach ($colors as $status => $color) {
            $svg .= '<linearGradient id="grad_' . $status . '" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" style="stop-color:' . $color[0] . ';stop-opacity:1" />
                <stop offset="100%" style="stop-color:' . $color[1] . ';stop-opacity:1" />
            </linearGradient>';
        }
        // Градиент для рамки
        $svg .= '<linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#6c757d;stop-opacity:1" /> <!-- Серый -->
            <stop offset="100%" style="stop-color:#0d6efd;stop-opacity:1" /> <!-- Синий -->
        </linearGradient>';
        $svg .= '</defs>';

        // Рамка с закругленными углами и градиентной заливкой
        $svg .= '<rect x="10" y="10" width="' . ($width - 20) . '" height="' . ($height - 20) . '" rx="15" ry="15" fill="url(#grad1)" stroke="#333" stroke-width="2" filter="url(#shadow)" />';

        // Тень для рамки
        $svg .= '<defs>
            <filter id="shadow">
                <feDropShadow dx="5" dy="5" stdDeviation="4" flood-color="#333" flood-opacity="0.5"/>
            </filter>
        </defs>';

        // Определение области для столбцов
        $availableWidth = $width - 40; // Ширина области для столбцов (учитывая отступы по краям)
        $totalBarWidth = count($statusCounts) * $barWidth + (count($statusCounts) - 1) * $padding; // Общая ширина столбцов с учетом промежутков
        $startX = ($width - $totalBarWidth) / 2; // Начальная координата X для центрирования столбцов

        // Визуализация столбиков и подписей
        $x = $startX; // Начальная координата X для столбцов

        foreach ($statusCounts as $status => $count) {
            if ($count > 0) {
                // Высота столбика пропорциональна количеству
                $barHeight = ($count / $total) * $maxBarHeight;
                $y = $height - $barHeight - 40; // Координата Y для размещения столбика

                // Создание столбика с градиентом и закругленными углами
                $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $barWidth . '" height="' . $barHeight . '" fill="url(#grad_' . $status . ')" rx="10" ry="10" />';

                // Добавление фона под текстом
                $svg .= '<rect x="' . ($x + 5) . '" y="' . ($height - 30) . '" width="' . ($barWidth - 10) . '" height="20" fill="#fff" stroke="#333" stroke-width="1" rx="5" ry="5" />';

                // Добавление подписи под столбиком
                $svg .= '<text x="' . ($x + $barWidth / 2) . '" y="' . ($height - 15) . '" font-size="12" fill="#333" text-anchor="middle">' . ucfirst($status) . '</text>';

                $x += $barWidth + $padding; // Перемещение для следующего столбика
            }
        }

        // Завершение SVG
        $svg .= '</svg>';

        // Сохранение SVG в файл
        $chartPath = storage_path('app/public/chart.svg');
        file_put_contents($chartPath, $svg);

        return $chartPath;
    }


}
