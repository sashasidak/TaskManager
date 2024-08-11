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

        $comment = $request->input('comment', '');

        $data = [
            'project' => $project,
            'testRun' => $testRun,
            'testPlan' => $testPlan,
            'repository' => $repository,
            'relatedSuites' => $relatedSuites,
            'testCases' => $testCases,
            'results' => $results,
            'comment' => $comment
        ];

        $pdf = SnappyPdf::loadView('pdf.suite_report', $data);
        return $pdf->download("TestRun_Report_{$testRun->id}.pdf");
    }
}
