<?php

namespace App\Http\Controllers;

use App\Enums\TestRunCaseStatus;
use App\Repository;
use App\TestCase;
use App\TestPlan;
use App\Project;
use App\TestRun;
use App\Suite;
use Illuminate\Http\Request;

class TestRunController extends Controller
{

    public function updateCaseStatus(Request $request)
    {
        $testRun = TestRun::findOrFail($request->test_run_id);
        $results = $testRun->getResults();
        $results[$request->test_case_id] = $request->status;
        $testRun->saveResults($results);
    }

    /*****************************************
     *  PAGES
     *****************************************/

    public function index($projectId)
    {
        // Найти проект и получить все тестовые запуски
        $project = Project::findOrFail($projectId);
        $testRuns = TestRun::where('project_id', $projectId)
                        ->orderBy('created_at', 'DESC') // Добавляем сортировку
                        ->get();
        // Инициализируем массив для хранения статусов тестов для каждого тестового запуска
        $testRunStatusCounts = [];

        foreach ($testRuns as $testRun) {
            $testPlan = TestPlan::findOrFail($testRun->test_plan_id);
            $testCasesIds = explode(',', $testPlan->data);
            $results = $testRun->getResults();

            // Подсчитать количество тест-кейсов по статусам
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

            // Сохранить в массиве результаты
            $testRunStatusCounts[$testRun->id] = $statusCounts;
        }

        return view('test_run.list_page', [
            'project' => $project,
            'testRuns' => $testRuns,
            'testRunStatusCounts' => $testRunStatusCounts,
        ]);
    }

    public function show($project_id, $test_run_id)
    {
        $project = Project::findOrFail($project_id);
        $testRun = TestRun::findOrFail($test_run_id);
        $testPlan = TestPlan::findOrFail($testRun->test_plan_id);
        $repository = Repository::findOrFail($testPlan->repository_id);

        $testCasesIds = explode(',', $testPlan->data);
        $testSuitesIds = TestCase::whereIn('id', $testCasesIds)->get()->pluck('suite_id')->toArray();

        $testSuitesTree = Suite::whereIn('id', $testSuitesIds)->tree()->get()->toTree();
        $suites = Suite::whereIn('id', $testSuitesIds)->orderBy('order')->get();

        $testRun->removeDeletedCasesFromResults();

        $results = $testRun->getResults();

        // Подсчитать количество тестов по статусам
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

            // Проверка наличия ссылки в описании
            $containsLink = strpos($testPlan->description, 'http://jira.ab.loc/browse/') !== false;


        return view('test_run.show_page')
            ->with('project', $project)
            ->with('testRun', $testRun)
            ->with('testPlan', $testPlan)
            ->with('repository', $repository)
            ->with('testSuitesTree', $testSuitesTree)
            ->with('suites', $suites)
            ->with('testCasesIds', $testCasesIds)
            ->with('results', $results)
            ->with('statusCounts', $statusCounts)
            ->with('containsLink', $containsLink)
            ->with('testPlanDescription', $testPlan->description); // Передача описания
}

    public function create($project_id)
    {

        $project = Project::findOrFail($project_id);
        $testPlans = TestPlan::all();

        return view('test_run.create_page')
            ->with('project', $project)
            ->with('testPlans', $testPlans);
    }

    public function edit($project_id, $test_run_id)
    {

        $project = Project::findOrFail($project_id);
        $testRun = TestRun::findOrFail($test_run_id);

        return view('test_run.edit_page')
            ->with('project', $project)
            ->with('testRun', $testRun);
    }

    public function filter(Request $request)
    {
        $searchTerm = $request->input('searchTerm');
        $testCases = TestCase::where('title', 'LIKE', '%' . $searchTerm . '%')->get();

        return view('test_run.filtered_test_cases')
            ->with('testCases', $testCases);
    }




    /*****************************************
     *  CRUD
     *****************************************/

    public function store(Request $request)
    {

        $request->validate([
            'title' => 'required',
            'test_plan_id' => 'required',
        ]);

        $testRun = new TestRun();
        $testRun->title = $request->title;
        $testRun->test_plan_id = $request->test_plan_id;
        $testRun->project_id = $request->project_id;
        $testRun->data = $testRun->getInitialData();
        $testRun->save();

        return redirect()->route('test_run_list_page', $request->project_id);
    }

    public function update(Request $request)
    {

        $testRun = TestRun::findOrFail($request->id);

        $testRun->title = $request->title;
        $testRun->save();

        return redirect()->route('test_run_show_page', [$testRun->project_id, $testRun->id]);
    }

    public function destroy(Request $request)
    {

        $testRun = TestRun::findOrFail($request->id);
        $testRun->delete();
        return redirect()->route('test_run_list_page', $request->project_id);
    }

    /*****************************************
     *  Test case load
     *****************************************/

    public function loadTestCase($test_run_id, $test_case_id)
    {
        $testRun = TestRun::findOrFail($test_run_id);
        $testCase = TestCase::findOrFail($test_case_id);
        $suite = Suite::findOrFail($testCase->suite_id);
        $repository = Repository::findOrFail($suite->repository_id);
        $data = json_decode($testCase->data);

        return view('test_run.test_case')
            ->with('repository', $repository)
            ->with('testCase', $testCase)
            ->with('testRun', $testRun)
            ->with('data', $data);
    }

    public function loadChart($test_run_id)
    {
        $testRun = TestRun::findOrFail($test_run_id);

        return view('test_run.chart')
            ->with('testRun', $testRun);
    }
}
