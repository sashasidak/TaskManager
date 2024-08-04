<?php

namespace App\Http\Controllers;

use App\Repository;
use App\TestPlan;
use App\Project;
use App\TestRun;
use App\Suite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TestPlanController extends Controller
{

    public function startNewTestRun($test_plan_id)
    {
        if(!auth()->user()->can('add_edit_test_runs')) {
            abort(403);
        }

        $testPlan = TestPlan::findOrFail($test_plan_id);

        $testRun = new TestRun();
        $testRun->title = 'Test Run';
        $testRun->test_plan_id = $testPlan->id;
        $testRun->project_id = $testPlan->project_id;
        $testRun->data = $testRun->getInitialData();
        $testRun->save();

        $testRun->title = $testPlan->title;
        $testRun->save();

        return redirect()->route('test_run_show_page', [$testPlan->project_id, $testRun->id]);
    }

    /*****************************************
     *  PAGES
     *****************************************/

    public function filter(Request $request)
    {
        $searchTerm = $request->input('searchTerm');
        $project = Project::findOrFail($request->project_id);

        $testPlans = TestPlan::where('project_id', $project->id)
            ->where('title', 'LIKE', '%' . $searchTerm . '%')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('test_plan.partials.test_plan_list')
            ->with('testPlans', $testPlans)
            ->with('project', $project);
    }
    public function index($project_id)
    {
        $project = Project::findOrFail($project_id);
        $testPlans = TestPlan::where('project_id', $project->id)->orderBy('created_at', 'DESC')->get();

        return view('test_plan.list_page')
            ->with('project', $project)
            ->with('testPlans', $testPlans);
    }

    public function create($project_id)
    {
        if(!auth()->user()->can('add_edit_test_plans')) {
            abort(403);
        }

        $project = Project::findOrFail($project_id);
        $repositories = $project->repositories;

        return view('test_plan.create_page')
            ->with('project', $project)
            ->with('repositories', $repositories);
    }

    public function edit($project_id, $test_plan_id)
    {
        if(!auth()->user()->can('add_edit_test_plans')) {
            abort(403);
        }

        $project = Project::findOrFail($project_id);
        $repositories = $project->repositories;
        $testPlan = TestPlan::findOrFail($test_plan_id);
        $testSuitesTree = Suite::where('repository_id', $testPlan->repository_id)->orderBy('order')->tree()->get()->toTree();
        $prefix = Repository::findOrFail($testPlan->repository_id)->prefix;

        return view('test_plan.edit_page')
            ->with('project', $project)
            ->with('testPlan', $testPlan)
            ->with('repositories', $repositories)
            ->with('prefix', $prefix)
            ->with('testSuitesTree', $testSuitesTree);
    }

    /*****************************************
     *  CRUD
     *****************************************/

    public function store(Request $request)
    {
        if(!auth()->user()->can('add_edit_test_plans')) {
            abort(403);
        }

        $request->validate([
            'title' => 'required',
        ]);

        $testPlan = new TestPlan();

        $testPlan->title = $request->title;
        $testPlan->project_id = $request->project_id;
        $testPlan->repository_id = $request->repository_id;
        $testPlan->description = $request->description;
        $testPlan->data = $request->data;  // это строка с id выбранных тест кейсов - 1,2,3 etc

        $testPlan->save();

        // Создание тест-рана и привязка его к тест-плану
        $testRun = new TestRun();
        $testRun->title = $testPlan->title;
        $testRun->test_plan_id = $testPlan->id;
        $testRun->project_id = $testPlan->project_id;
        $testRun->data = $testRun->getInitialData();
        $testRun->save();

        return redirect()->route('test_run_show_page', [$testPlan->project_id, $testRun->id]);
    }

    public function update(Request $request)
    {
        if(!auth()->user()->can('add_edit_test_plans')) {
            abort(403);
        }

    // Находим тест план по его ID
        $testPlan = TestPlan::findOrFail($request->id);

    // Сохраняем старое название тест плана
        $oldTitle = $testPlan->title;

    // Обновляем тест план новыми данными
        $testPlan->title = $request->title;
        $testPlan->description = $request->description;
        $testPlan->repository_id = $request->repository_id;
        $testPlan->data = $request->data;  // это строка с id выбранных тест кейсов - 1,2,3 etc

        $testPlan->save();

    // Обновляем название тест ранов, ассоциированных с тест планом
        TestRun::where('test_plan_id', $testPlan->id)
        ->update([
            'title' => $testPlan->title,
        ]);
    // Получаем первый тест-ран, привязанный к тест-плану
        $testRun = TestRun::where('test_plan_id', $testPlan->id)->first();

    // Проверяем, есть ли привязанный тест-ран, и делаем редирект на его страницу
        if ($testRun) {
        return redirect()->route('test_run_show_page', [$testPlan->project_id, $testRun->id])->with('success', 'Test Plan updated successfully');
    }
    return redirect()->route('test_run_list_page', [$request->project_id, $request->id]);
}

    public function destroy(Request $request)
    {
        if(!auth()->user()->can('delete_test_plans')) {
            abort(403);
        }

        $testPlan = TestPlan::findOrFail($request->id);
        $project_id = $testPlan->project_id;
        $testPlan->delete();
        return redirect()->route('test_run_list_page', $project_id);
    }

    /*****************************************
     *  HTML js load
     *****************************************/

    public function loadRepoTree($repository_id)
    {
        $repository = Repository::findOrFail($repository_id);
        $project = Project::findOrFail($repository->project_id);
        $testSuitesTree = Suite::where('repository_id', $repository_id)->orderBy('order')->tree()->get()->toTree();

        return view('test_plan.tree')
            ->with('repository', $repository)
            ->with('prefix', $repository->prefix)
            ->with('testSuitesTree', $testSuitesTree)
            ->with('project', $project);
    }
}
