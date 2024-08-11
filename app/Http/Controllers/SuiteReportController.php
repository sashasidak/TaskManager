<?php
namespace App\Http\Controllers;

use App\Project;
use App\Repository;
use App\Suite;
use App\TestPlan;
use App\TestRun;
use App\TestCase;
use Barryvdh\Snappy\Facades\SnappyPdf;

class SuiteReportController extends Controller
{
    public function generatePdf($project_id, $test_run_id, $suite_id)
    {
        $project = Project::findOrFail($project_id);
        $testRun = TestRun::findOrFail($test_run_id);
        $testPlan = TestPlan::findOrFail($testRun->test_plan_id);
        $repository = Repository::findOrFail($testPlan->repository_id);

        $testCasesIds = explode(',', $testPlan->data);
        $testSuitesIds = TestCase::whereIn('id', $testCasesIds)->get()->pluck('suite_id')->toArray();

        // Фильтруем по выбранному suite_id
        $suites = Suite::where('id', $suite_id)->get();
        $testSuitesTree = Suite::whereIn('id', $testSuitesIds)->tree()->get()->toTree();

        // Выбираем тесты только из выбранного suite_id
        $testCases = TestCase::whereIn('suite_id', $suites->pluck('id'))->whereIn('id', $testCasesIds)->get();
        $results = $testRun->getResults();

        $data = [
            'project' => $project,
            'testRun' => $testRun,
            'testPlan' => $testPlan,
            'repository' => $repository,
            'testSuitesTree' => $testSuitesTree,
            'suites' => $suites,
            'testCases' => $testCases,
            'results' => $results
        ];

        $pdf = SnappyPdf::loadView('pdf.suite_report', $data);
        return $pdf->download("TestRun_Report_{$testRun->id}.pdf");
    }

}

