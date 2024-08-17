<?php

namespace App\Http\Controllers;

use App\Suite;
use App\Repository;
use App\TestCase;
use Illuminate\Http\Request;

class TaskCopyController extends Controller
{
    public function copyTasks(Request $request, $sourceRepoId, $targetRepoId)
        {
            // Получение репозиториев
            $sourceRepo = Repository::findOrFail($sourceRepoId);
            $targetRepo = Repository::findOrFail($targetRepoId);

            // Получение всех связанных задач и подзадач из исходного репозитория
            $sourceSuites = Suite::where('repository_id', $sourceRepo->id)
                                 ->orWhereIn('id', Suite::where('repository_id', $sourceRepo->id)->pluck('id'))
                                 ->get();

            // Получение всех тест-кейсов, связанных с задачами и подзадачами исходного репозитория
            $testCases = TestCase::whereIn('suite_id', $sourceSuites->pluck('id'))->get();

            // Копирование задач в целевой репозиторий
            foreach ($sourceSuites as $suite) {
                // Создаем новый объект Suite
                $newSuite = $suite->replicate();
                $newSuite->repository_id = $targetRepo->id;
                $newSuite->save();

                // Копирование тест-кейсов для каждого скопированного suite
                $suitesTestCases = $testCases->where('suite_id', $suite->id);
                foreach ($suitesTestCases as $testCase) {
                    $newTestCase = $testCase->replicate();
                    $newTestCase->suite_id = $newSuite->id;
                    $newTestCase->save();
                }
            }

            return response()->json(['message' => 'Tasks copied successfully.']);
        }

}
