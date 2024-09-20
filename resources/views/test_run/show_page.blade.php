@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    {{-- TEST SUITES TREE COLUMN --}}
    <div class="col shadow-sm" style="max-width: 700px">

        {{-- COLUMN header --}}
        <div class="border-bottom mt-2 pb-2 mb-2 d-flex justify-content-between">
            <span class="fs-5">
                {{$testRun->title}}
            </span>

            @can('add_edit_test_runs')
                <div class="d-flex flex-wrap align-items-center">
                    <button class="btn btn-sm btn-outline-dark me-1" id="filter-button" title="Filter">
                        <i class="bi bi-funnel"></i>
                    </button>
                    <a href="{{ route('test_plan_update_page', [$project->id, $testPlan->id]) }}"
                       class="btn btn-sm btn-outline-dark me-1"
                       title="Repository Settings">
                        <i class="bi bi-gear"></i>
                    </a>
                    <!-- Collapse/Expand All Button -->
                    <button class="btn btn-sm btn-outline-dark me-1" id="toggle-all-button" title="Collapse/Expand All">
                        <i class="bi bi-chevron-down"></i>
                    </button>

                    @if ($containsLink)
                    <!-- Bug Report Button -->
                    <button class="btn btn-sm btn-outline-danger me-1" id="bug-report-button" title="Bug Report">
                        <i class="bi bi-bug"></i>
                    </button>
                    @endif

                    {{-- Подключаем overlay --}}
                    @include('jira.bug_report_overlay')
                </div>
            @endcan

        </div>

        <div id="filter-input-container" style="display: none; margin-bottom: 10px;">
            <input type="text" id="filter-input" class="form-control" placeholder="Введите для фильтрации...">
        </div>

        <!-- Progress Bar -->
        <div class="progress mb-1">
            @php
                $total = array_sum($statusCounts);
                $widths = [];
                if ($total > 0) {
                    foreach (['passed', 'failed', 'blocked', 'not_tested'] as $status) {
                        $widths[$status] = ($statusCounts[$status] / $total) * 100;
                    }
                }
            @endphp
            <div class="progress-bar bg-success position-relative" role="progressbar" style="width: {{ $widths['passed'] ?? 0 }}%;">
                <span class="text-white position-absolute w-100 text-center" style="font-size: 0.9rem;">{{ $statusCounts['passed'] }}</span>
            </div>
            <div class="progress-bar bg-danger position-relative" role="progressbar" style="width: {{ $widths['failed'] ?? 0 }}%;">
                <span class="text-white position-absolute w-100 text-center" style="font-size: 0.9rem;">{{ $statusCounts['failed'] }}</span>
            </div>
            <div class="progress-bar bg-warning position-relative" role="progressbar" style="width: {{ $widths['blocked'] ?? 0 }}%;">
                <span class="text-white position-absolute w-100 text-center" style="font-size: 0.9rem;">{{ $statusCounts['blocked'] }}</span>
            </div>
            <div class="progress-bar bg-secondary position-relative" role="progressbar" style="width: {{ $widths['not_tested'] ?? 0 }}%;">
                <span class="text-white position-absolute w-100 text-center" style="font-size: 0.9rem;">{{ $statusCounts['not_tested'] }}</span>
            </div>
        </div>

        <div id="tree">
            @include('test_run.test_cases_list')
        </div>

    </div>

    <div class="col" id="test_case_col">
        <div class="fs-5 border-bottom mt-2 pb-2 mb-2">
            Select Test Case
        </div>
    </div>

@endsection

@section('footer')

    <script src="{{ asset('js/test_run.js') }}"></script>

    <script>
        $("#filter-button").click(function() {
            $("#filter-input-container").toggle();
        });

        $("#filter-input").on('input', function() {
            let searchTerm = $(this).val().toLowerCase();

            $(".suite_header").each(function() {
                let suiteTitle = $(this).text().toLowerCase();

                // Проверяем совпадение в заголовке suite
                if (suiteTitle.includes(searchTerm)) {
                    $(this).show(); // Показываем соответствующий заголовок
                    $(this).nextUntil('.suite_header').show(); // Показываем все элементы между заголовками
                } else {
                    $(this).hide(); // Скрываем заголовок
                    $(this).nextUntil('.suite_header').hide(); // Скрываем все элементы между заголовками
                }
            });

            // Обеспечиваем видимость заголовка, если у него есть следующие элементы
            $(".suite_header:visible").each(function() {
                $(this).nextUntil('.suite_header').show();
            });
        });

        $(".badge.bg-secondary").first().click(); // select first untested case

        // Function to toggle individual test cases
        function toggleTestCases(button) {
            var testCases = button.closest('.suite_header').nextElementSibling;
            if (testCases.style.display === "none") {
                testCases.style.display = "block";
                button.innerHTML = '<i class="bi bi-chevron-down"></i>';
            } else {
                testCases.style.display = "none";
                button.innerHTML = '<i class="bi bi-chevron-right"></i>';
            }
        }

        // Function to toggle all test cases (collapse/expand)
        function toggleAllTestCases() {
            const isCollapsed = $("#toggle-all-button").data("collapsed");
            $(".suite_header").each(function() {
                const testCases = $(this).nextUntil('.suite_header');

                if (isCollapsed) {
                    testCases.show(); // Expand
                    $(this).find('.toggle-button i').removeClass('bi-chevron-right').addClass('bi-chevron-down');
                } else {
                    testCases.hide(); // Collapse
                    $(this).find('.toggle-button i').removeClass('bi-chevron-down').addClass('bi-chevron-right');
                }
            });

            // Toggle the state
            $("#toggle-all-button").data("collapsed", !isCollapsed);

            // Update the icon on the global button
            const icon = isCollapsed ? "bi-chevron-down" : "bi-chevron-right";
            $("#toggle-all-button i").attr("class", `bi ${icon}`);
        }

        // Attach the toggle function to the button
        $("#toggle-all-button").click(toggleAllTestCases);
        // Initially set collapsed state to false
        $("#toggle-all-button").data("collapsed", false);
    </script>
    <script>
            $(document).ready(function() {
                // Получение описания из Blade-шаблона
                var description = @json($testPlanDescription);

                function extractIssueKey(description) {
                    // Регулярное выражение для извлечения ключа задачи
                    var regex = /http:\/\/jira\.ab\.loc\/browse\/(\w+-\d+)/;
                    var match = description.match(regex);
                    return match ? match[1] : null;
                }

                // Получаем ключ задачи
                var issueKey = extractIssueKey(description);
                if (issueKey) {
                    console.log("Issue Key:", issueKey);

                    // Показать кнопку Bug Report и добавить обработчик клика
                    var bugReportButton = document.getElementById('bug-report-button');
                    if (bugReportButton) {
                        bugReportButton.style.display = 'block';

                        bugReportButton.addEventListener('click', function() {
                            var issueKeyInput = document.querySelector('#issueKey');
                            if (issueKeyInput) {
                                issueKeyInput.value = issueKey;
                            }

                            // Открыть оверлей с формой багрепорта
                            var overlay = document.querySelector('.overlay');
                            if (overlay) {
                                overlay.style.display = 'block';
                            }
                        });
                    }
                } else {
                    console.log("Issue Key not found.");
                }
            });
        </script>
<style>
#bug-report-button {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

#bug-report-button i {
    pointer-events: none;
}
</style>
@endsection
