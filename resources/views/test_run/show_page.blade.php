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
                <div>
                    <button class="btn btn-sm btn-outline-dark me-1" id="filter-button" title="Filter">
                        <i class="bi bi-funnel"></i>
                    </button>
                    <a href="{{ route('test_plan_update_page', [$project->id, $testPlan->id]) }}"
                       class="btn btn-sm btn-outline-dark me-1"
                       title="Repository Settings">
                        <i class="bi bi-gear"></i>
                    </a>
                </div>
            @endcan

        </div>

        <div id="filter-input-container" style="display: none; margin-bottom: 10px;">
            <input type="text" id="filter-input" class="form-control" placeholder="Введите для фильтрации...">
        </div>

        <div class="pb-2" id="chart">
            @include('test_run.chart')
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
    </script>

@endsection
