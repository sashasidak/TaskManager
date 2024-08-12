<?php
namespace App\Http\Controllers;

use App\Project;
use App\Repository;
use App\Suite;
use App\TestPlan;
use App\TestRun;
use App\TestCase;
use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf;

class SuiteReportController extends Controller
{
    public function generatePdf(Request $request, $project_id, $test_run_id, $suite_id)
    {
        $project = Project::findOrFail($project_id);
        $testRun = TestRun::findOrFail($test_run_id);
        $testPlan = TestPlan::findOrFail($testRun->test_plan_id);
        $repository = Repository::findOrFail($testPlan->repository_id);

        $testCasesIds = explode(',', $testPlan->data);
        $testSuitesIds = TestCase::whereIn('id', $testCasesIds)->get()->pluck('suite_id')->toArray();

        // Получение всех связанных задач и подзадач
        $suite = Suite::findOrFail($suite_id);
        $relatedSuites = Suite::whereIn('id', $suite->descendantsAndSelf()->pluck('id')->toArray())->get();

        // Получение всех тест-кейсов, связанных с задачами и подзадачами
        $testCases = TestCase::whereIn('suite_id', $relatedSuites->pluck('id'))->whereIn('id', $testCasesIds)->get();
        $results = $testRun->getResults();

        // Подсчет количества тест-кейсов по статусам
        $statusCounts = [
            'passed' => 0,
            'failed' => 0,
            'blocked' => 0,
            'not_tested' => 0,
        ];

        foreach ($testCases as $testCase) {
            $status = $results[$testCase->id] ?? 4;
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

        // Генерация диаграммы
        $chartImagePath = $this->generateChart($statusCounts);

        $comment = $request->input('comment', '');

        $data = [
            'project' => $project,
            'testRun' => $testRun,
            'testPlan' => $testPlan,
            'repository' => $repository,
            'relatedSuites' => $relatedSuites,
            'testCases' => $testCases,
            'results' => $results,
            'comment' => $comment,
            'chartImagePath' => $chartImagePath,
        ];

        $pdf = SnappyPdf::loadView('pdf.suite_report', $data);
        $pdf->setOption('enable-local-file-access', true);
        return $pdf->download("TestRun_Report_{$testRun->id}.pdf");
    }

    private function generateChart($statusCounts)
    {
        $total = array_sum($statusCounts);
        $width = 600; // Ширина диаграммы
        $height = 400; // Высота диаграммы
        $barWidth = 80; // Ширина одного столбика
        $padding = 15;   // Промежуток между столбиками
        $maxBarHeight = $height - 100; // Максимальная высота столбика

        // Цвета с градиентами
        $colors = [
            'passed' => ['#28a745', '#218838'], // Зелёный градиент
            'failed' => ['#dc3545', '#c82333'], // Красный градиент
            'blocked' => ['#ffc107', '#e0a800'], // Жёлтый градиент
            'not_tested' => ['#6c757d', '#5a6268'] // Серый градиент
        ];

        // Начало SVG с рамкой
        $svg = '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">';

        // Градиент для заливки рамки
        $svg .= '<defs>
                <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#6c757d;stop-opacity:1" /> <!-- Серый -->
                    <stop offset="100%" style="stop-color:#0d6efd;stop-opacity:1" /> <!-- Синий -->
                </linearGradient>';

        // Создание градиентов для столбиков
        foreach ($colors as $status => $color) {
            $svg .= '<linearGradient id="grad_' . $status . '" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" style="stop-color:' . $color[0] . ';stop-opacity:1" />
                    <stop offset="100%" style="stop-color:' . $color[1] . ';stop-opacity:1" />
                 </linearGradient>';
        }

        $svg .= '</defs>';

        // Рамка с закругленными углами и тенью
        $svg .= '<rect x="10" y="10" width="' . ($width - 20) . '" height="' . ($height - 20) . '" rx="15" ry="15" fill="url(#grad1)" stroke="#333" stroke-width="2" filter="url(#shadow)" />';

        // Тень для рамки
        $svg .= '<defs>
                <filter id="shadow">
                    <feDropShadow dx="5" dy="5" stdDeviation="4" flood-color="#333" flood-opacity="0.5"/>
                </filter>
            </defs>';

        $x = ($width - (count($statusCounts) * ($barWidth + $padding) - $padding)) / 2; // Начальная координата X

        foreach ($statusCounts as $status => $count) {
            if ($count > 0) {
                // Высота столбика пропорциональна количеству
                $barHeight = ($count / $total) * $maxBarHeight;
                $y = $height - $barHeight - 50; // Координата Y для размещения столбика

                // Создание столбика с градиентом и закругленными углами
                $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $barWidth . '" height="' . $barHeight . '" fill="url(#grad_' . $status . ')" rx="10" ry="10" />';

                // Добавление тени к столбику
                $svg .= '<filter id="barShadow">
                        <feDropShadow dx="3" dy="3" stdDeviation="2" flood-color="#000" flood-opacity="0.3"/>
                    </filter>';

                // Применение тени
                $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $barWidth . '" height="' . $barHeight . '" fill="none" filter="url(#barShadow)" />';

                // Подпись
                $svg .= '<text x="' . ($x + $barWidth / 2) . '" y="' . ($height - 20) . '" font-family="Arial" font-size="16" fill="#000" text-anchor="middle">' . ucfirst($status) . '</text>';

                $x += $barWidth + $padding; // Смещение для следующего столбика
            }
        }

        // Завершение SVG
        $svg .= '</svg>';

        $imagePath = storage_path('app/public/status_chart.svg');
        file_put_contents($imagePath, $svg);

        return $imagePath;
    }



}
