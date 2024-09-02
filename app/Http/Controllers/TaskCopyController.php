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
        $targetSuiteId = $request->input('target_suite_id');

        // Массив для отслеживания старых и новых задач (включая подзадачи)
        $suiteMapping = [];

        // Функция для рекурсивного копирования задач и их подзадач
        $copySuite = function ($suite, $newParentId = null) use ($targetRepo, &$suiteMapping, &$copySuite, $testCaseIds) {
            // Создаем копию задачи
            $newSuite = $suite->replicate();
            $newSuite->repository_id = $targetRepo->id;
            $newSuite->parent_id = $newParentId;

            if ($newSuite->save()) {
                \Log::info('Copied Suite:', [$newSuite->id]);

                // Сохраняем сопоставление старой и новой задачи
                $suiteMapping[$suite->id] = $newSuite->id;

                // Копируем подзадачи рекурсивно
                $childSuites = Suite::where('parent_id', $suite->id)->get();
                foreach ($childSuites as $childSuite) {
                    $copySuite($childSuite, $newSuite->id);
                }

                // Копируем тест-кейсы только если они выбраны
                if (!empty($testCaseIds)) {
                    $testCases = TestCase::where('suite_id', $suite->id)->whereIn('id', $testCaseIds)->get();
                    foreach ($testCases as $testCase) {
                        $newTestCase = $testCase->replicate();
                        $newTestCase->suite_id = $newSuite->id;

                        if ($newTestCase->save()) {
                            \Log::info('Copied Test Case:', [$newTestCase->id]);
                        } else {
                            \Log::error('Failed to Copy Test Case:', [$testCase->id]);
                        }
                    }
                }
            } else {
                \Log::error('Failed to Copy Suite:', [$suite->id]);
            }
        };

        // Копирование задач, если они указаны
        if (!empty($suiteIds)) {
            $sourceSuites = Suite::whereIn('id', $suiteIds)->get();
            foreach ($sourceSuites as $suite) {
                $copySuite($suite);
            }
        }

        // Копирование тест-кейсов в существующую задачу
        if (!empty($testCaseIds) && !empty($targetSuiteId)) {
            // Проверяем существование целевой задачи
            $targetSuite = Suite::find($targetSuiteId);
            if (!$targetSuite) {
                \Log::error('Target Suite not found:', ['id' => $targetSuiteId]);
                return response()->json(['message' => 'Target Suite not found.'], 400);
            }

            \Log::info('Target Suite Found:', [$targetSuiteId]);

            // Получаем тест-кейсы из исходного репозитория
            $sourceTestCases = TestCase::whereIn('id', $testCaseIds)->get();
            foreach ($sourceTestCases as $testCase) {
                $newTestCase = $testCase->replicate();
                $newTestCase->suite_id = $targetSuite->id;

                if ($newTestCase->save()) {
                    \Log::info('Copied Test Case:', [$newTestCase->id]);
                } else {
                    \Log::error('Failed to Copy Test Case:', [$testCase->id]);
                }
            }

            return response()->json(['message' => 'Test cases copied successfully.']);
        }

        return response()->json(['message' => 'Selected tasks and test cases copied successfully.']);
    }


    private function copySuiteWithChildren($suite, $targetRepoId, $testCaseIds)
    {
        // Копируем основную задачу
        $newSuite = $suite->replicate();
        $newSuite->repository_id = $targetRepoId;
        $newSuite->parent_id = $suite->parent_id; // Сохраняем родительскую задачу

        if ($newSuite->save()) {
            \Log::info('Copied Suite:', [$newSuite->id]);

            // Копируем тест-кейсы, если они принадлежат текущей задаче
            $testCases = TestCase::where('suite_id', $suite->id)
                                 ->whereIn('id', $testCaseIds)
                                 ->get();

            foreach ($testCases as $testCase) {
                $newTestCase = $testCase->replicate();
                $newTestCase->suite_id = $newSuite->id;

                if ($newTestCase->save()) {
                    \Log::info('Copied Test Case to Suite:', [$newTestCase->id]);
                } else {
                    \Log::error('Failed to Copy Test Case to Suite:', [$testCase->id]);
                }
            }

            // Находим подзадачи и копируем их рекурсивно
            $childSuites = Suite::where('parent_id', $suite->id)->get();
            foreach ($childSuites as $childSuite) {
                $this->copySuiteWithChildren($childSuite, $targetRepoId, $testCaseIds);
            }
        } else {
            \Log::error('Failed to Copy Suite:', [$suite->id]);
        }
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
