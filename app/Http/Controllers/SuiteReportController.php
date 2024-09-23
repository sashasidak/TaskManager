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
        $phoneFieldData = $request->input('smartphoneData', ''); // Получение данных из поля смартфона

        $data = [
            'project' => $project,
            'testRun' => $testRun,
            'testPlan' => $testPlan,
            'repository' => $repository,
            'relatedSuites' => $relatedSuites,
            'testCases' => $testCases,
            'results' => $results,
            'comment' => $comment,
            'suite' => $suite,
            'phoneFieldData' => $phoneFieldData, // Передача данных поля смартфона в представление
            'chartImagePath' => $chartImagePath,
        ];

        $pdf = SnappyPdf::loadView('pdf.suite_report', $data);
        $pdf->setOption('enable-local-file-access', true);
        return $pdf->download("TestRun_Report_{$testRun->id}.pdf");
    }

    private function generateChart($statusCounts)
    {
        $total = array_sum($statusCounts);
        $width = 800; // Ширина диаграммы и таблицы
        $height = 300; // Высота диаграммы и таблицы
        $barWidth = 80; // Ширина одного столбика
        $padding = 20;   // Промежуток между столбиками
        $maxBarHeight = $height - 100; // Максимальная высота столбика
        $tableWidth = 250; // Ширина таблицы
        $tablePadding = 30; // Отступы таблицы
        $tableHeight = $height - 60; // Высота таблицы

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
            <stop offset="0%" style="stop-color:#f8f9fa;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#e9ecef;stop-opacity:1" />
        </linearGradient>';
        $svg .= '</defs>';

        // Рамка с закругленными углами и градиентной заливкой
        $svg .= '<rect x="10" y="10" width="' . ($width - 20) . '" height="' . ($height - 20) . '" rx="20" ry="20" fill="url(#grad1)" stroke="#ccc" stroke-width="2" filter="url(#shadow)" />';

        // Тень для рамки
        $svg .= '<defs>
            <filter id="shadow">
                <feDropShadow dx="3" dy="3" stdDeviation="5" flood-color="#aaa" flood-opacity="0.5"/>
            </filter>
        </defs>';

        // Визуализация столбиков
        $x = 30; // Начальная координата X

        foreach ($statusCounts as $status => $count) {
            if ($count > 0) {
                // Высота столбика пропорциональна количеству
                $barHeight = ($count / $total) * $maxBarHeight;
                $y = $height - $barHeight - 50; // Координата Y для размещения столбика

                // Создание столбика с градиентом и закругленными углами
                $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $barWidth . '" height="' . $barHeight . '" fill="url(#grad_' . $status . ')" rx="10" ry="10" />';

                // Подпись под столбиком
                $svg .= '<text x="' . ($x + $barWidth / 2) . '" y="' . ($height - 20) . '" font-family="Arial, sans-serif" font-size="18" fill="#333" text-anchor="middle" font-weight="bold">' . ucfirst($status) . '</text>';

                $x += $barWidth + $padding; // Смещение для следующего столбика
            }
        }

        // Визуализация таблицы статистики
        $tableX = $width - $tableWidth - $tablePadding;
        $tableY = 20;

        $svg .= '<rect x="' . $tableX . '" y="' . $tableY . '" width="' . $tableWidth . '" height="' . $tableHeight . '" fill="#ffffff" stroke="#ddd" stroke-width="1" rx="15" ry="15" filter="url(#tableShadow)" />';
        $svg .= '<text x="' . ($tableX + $tableWidth / 2) . '" y="' . ($tableY + 30) . '" font-family="Arial, sans-serif" font-size="24" fill="#333" text-anchor="middle" font-weight="bold">Статистика</text>';

        $cardY = $tableY + 60;
        $cardHeight = 40; // Высота карточки
        $cardSpacing = 5; // Расстояние между карточками

        foreach ($statusCounts as $status => $count) {
            if ($cardY + $cardHeight > $tableY + $tableHeight) break; // Проверка, не выходит ли карточка за границы таблицы

            // Добавление тени для карточек
            $svg .= '<defs>
                <filter id="tableShadow">
                    <feDropShadow dx="2" dy="2" stdDeviation="2" flood-color="#aaa" flood-opacity="0.4"/>
                </filter>
            </defs>';

            $svg .= '<rect x="' . $tableX . '" y="' . $cardY . '" width="' . $tableWidth . '" height="' . $cardHeight . '" fill="url(#grad_' . $status . ')" rx="8" ry="8" />';
            $svg .= '<text x="' . ($tableX + $tableWidth / 2) . '" y="' . ($cardY + $cardHeight / 2 + 5) . '" font-family="Arial, sans-serif" font-size="18" fill="#ffffff" text-anchor="middle" font-weight="bold">' . ucfirst($status) . ': ' . $count . '</text>';
            $cardY += $cardHeight + $cardSpacing; // Смещение для следующей карточки
        }

        // Завершение SVG
        $svg .= '</svg>';

        $imagePath = storage_path('app/public/status_chart.svg');
        file_put_contents($imagePath, $svg);

        return $imagePath;
    }


}
