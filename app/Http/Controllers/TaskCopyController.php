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
        // Логируем все входные данные
        \Log::info('Request Data:', $request->all());

        // Находим исходный и целевой репозитории
        $sourceRepo = Repository::findOrFail($sourceRepoId);
        $targetRepo = Repository::findOrFail($targetRepoId);

        // Получаем данные из запроса
        $suiteIds = $request->input('suite_ids', []);
        $testCaseIds = $request->input('test_case_ids', []);
        $targetSuiteIds = $request->input('target_suite_ids', []);

        \Log::info('Source Repository ID:', [$sourceRepoId]);
        \Log::info('Target Repository ID:', [$targetRepoId]);
        \Log::info('Suite IDs:', $suiteIds);
        \Log::info('Test Case IDs:', $testCaseIds);
        \Log::info('Target Suite IDs:', $targetSuiteIds);

        // Если указаны только тест-кейсы и целевые задачи
            if (!empty($testCaseIds) && !empty($targetSuiteIds)) {
                foreach ($targetSuiteIds as $targetSuiteId) {
                    // Проверяем существование целевой задачи
                    $targetSuite = Suite::find($targetSuiteId);
                    if (!$targetSuite) {
                        \Log::error('Target Suite not found:', ['id' => $targetSuiteId]);
                        continue;
                    }

                    \Log::info('Target Suite Found:', [$targetSuiteId]);

                    // Получаем тест-кейсы из исходного репозитория
                    $sourceTestCases = TestCase::whereIn('id', $testCaseIds)->get();

                    \Log::info('Source Test Cases:', $sourceTestCases->toArray());

                    // Копирование тест-кейсов в целевую задачу
                    foreach ($sourceTestCases as $testCase) {
                        $newTestCase = $testCase->replicate();
                        $newTestCase->suite_id = $targetSuite->id;
                        // Не указываем repository_id, если оно не нужно в этой таблице
                        // $newTestCase->repository_id = $targetRepo->id; // Возможно, эта строка не нужна

                        if ($newTestCase->save()) {
                            \Log::info('Copied Test Case:', [$newTestCase->id]);
                        } else {
                            \Log::error('Failed to Copy Test Case:', [$testCase->id]);
                        }
                    }
                }

                return response()->json(['message' => 'Test cases copied successfully.']);
            }

        // Если указаны только задачи
        if (!empty($suiteIds) && empty($testCaseIds)) {
            $sourceSuites = Suite::whereIn('id', $suiteIds)->get();

            foreach ($sourceSuites as $suite) {
                $newSuite = $suite->replicate();
                $newSuite->repository_id = $targetRepo->id;

                if ($newSuite->save()) {
                    \Log::info('Copied Suite:', [$newSuite->id]);
                } else {
                    \Log::error('Failed to Copy Suite:', [$suite->id]);
                }
            }

            return response()->json(['message' => 'Selected tasks copied successfully.']);
        }

        // Если указаны задачи и тест-кейсы
        if (!empty($suiteIds) && !empty($testCaseIds)) {
            $sourceSuites = Suite::whereIn('id', $suiteIds)->get();

            foreach ($sourceSuites as $suite) {
                $newSuite = $suite->replicate();
                $newSuite->repository_id = $targetRepo->id;

                if ($newSuite->save()) {
                    \Log::info('Copied Suite:', [$newSuite->id]);

                    $testCases = TestCase::whereIn('id', $testCaseIds)->where('suite_id', $suite->id)->get();
                    foreach ($testCases as $testCase) {
                        $newTestCase = $testCase->replicate();
                        $newTestCase->suite_id = $newSuite->id;

                        if ($newTestCase->save()) {
                            \Log::info('Copied Test Case:', [$newTestCase->id]);
                        } else {
                            \Log::error('Failed to Copy Test Case:', [$testCase->id]);
                        }
                    }
                } else {
                    \Log::error('Failed to Copy Suite:', [$suite->id]);
                }
            }

            return response()->json(['message' => 'Selected tasks and test cases copied successfully.']);
        }

        return response()->json(['message' => 'No valid data to copy.'], 400);
    }

    public function getTestCasesBySuite($suiteId)
    {
        $testCases = TestCase::where('suite_id', $suiteId)->get();
        return response()->json(['test_cases' => $testCases]);
    }

    public function getSuitesByRepository($repositoryId)
    {
        $suites = Suite::where('repository_id', $repositoryId)->get();
        return response()->json(['suites' => $suites]);
    }

}
