@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    <div class="col">
        <div class="border-bottom my-3">
            <h3 class="page_title">
                Test Plans

                @can('add_edit_test_plans')
                    <a class="mx-3" href="{{ route('test_plan_create_page', $project->id) }}">
                        <button type="button" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg"></i> New Test Plan
                        </button>
                    </a>
                @endcan
            </h3>
        </div>

        <!-- Filter input field always visible -->
        <div id="filter-input-container" style="margin-bottom: 10px;">
            <input type="text" id="filter-input" class="form-control" placeholder="Filter test plans...">
        </div>

        <div class="row row-cols-1 row-cols-md-2 g-3">
            @foreach($testPlans as $testPlan)
                <div class="col test-plan-item">
                    <div class="base_block shadow-sm border h-100 rounded">
                        <div class="card-body d-flex justify-content-between pb-0">
                            <div>
                                <h4 class="card-title">{{ $testPlan->title }}</h4>
                            </div>

                            <div>
                                 <span>
                                    @if($testPlan->data) <b>{{ count(explode(",", $testPlan->data)) }}</b> @else 0 @endif test cases
                                 </span> |
                                <span class="text-muted" title="created at">{{ $testPlan->created_at->format('d-m-Y') }}</span>
                            </div>
                        </div>

                        @if($testPlan->description)
                            <div class="card-text text-muted ps-3">
                               <span>{!! preg_replace(
                                   '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#',
                                   '<a href="$0" target="_blank">$0</a>',
                                   e($testPlan->description)
                               ) !!}</span>
                            </div>
                        @endif

                        <div class="d-flex justify-content-end align-items-end border-top py-2">
                            <div>
                                @can('add_edit_test_runs')
                                    <a href="{{ route('start_new_test_run', $testPlan->id) }}" class="link-light btn btn-sm btn-success">
                                        <i class="bi bi-play-circle"></i>
                                        Start new test run
                                    </a>
                                @endcan

                                @can('add_edit_test_plans')
                                    <a href="{{ route('test_plan_update_page', [$project->id, $testPlan->id]) }}" class="btn btn-sm btn-outline-dark mx-3">
                                        <i class="bi bi-pencil"></i>
                                        Edit
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

@endsection

@section('footer')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#filter-input").on('input', function() {
                let searchTerm = $(this).val().toLowerCase();

                $(".test-plan-item").each(function() {
                    let title = $(this).find('.card-title').text().toLowerCase();

                    // Проверяем совпадение в заголовке карточки
                    if (title.includes(searchTerm)) {
                        $(this).show(); // Показываем карточку, если есть совпадение
                    } else {
                        $(this).hide(); // Скрываем карточку, если нет совпадения
                    }
                });
            });
        });
    </script>

@endsection
