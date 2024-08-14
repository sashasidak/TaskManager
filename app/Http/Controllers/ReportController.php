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

        // Найти все задачи (сuites), связанные с тест-кейсами
        $suiteIds = TestCase::whereIn('id', $testCasesIds)->pluck('suite_id')->unique();
        $suites = Suite::whereIn('id', $suiteIds)->get();

        // Подсчитать количество основных задач (сuites), игнорируя подзадачи
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
        ];

        // Генерация PDF
        $pdf = SnappyPdf::loadView('pdf.test_run_report', $data);
        $pdf->setOption('enable-local-file-access', true);

        return $pdf->download("TestRun_Report_{$testRun->id}.pdf");
    }
}

