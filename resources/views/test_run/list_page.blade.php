@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    <div class="col">
        <div class="border-bottom my-3">
            <h3 class="page_title">
                Test Runs

                @can('add_edit_test_runs')
                    <a class="mx-3" href="{{ route('test_plan_create_page', $project->id) }}">
                        <button type="button" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg"></i> New Test Run
                        </button>
                    </a>
                @endcan
            </h3>
        </div>

        <!-- Filter input field always visible -->
        <div id="filter-input-container" style="margin-bottom: 10px;">
            <input type="text" id="filter-input" class="form-control" placeholder="Filter test runs...">
        </div>

        <div class="row row-cols-1 row-cols-md-3 g-3">
            @foreach($testRuns as $testRun)
                <div class="col test-run-item">
                    <div class="base_block shadow h-100 rounded border">
                        <div class="card-body d-flex justify-content-between">
                            <div>
                                <a class="fs-4" href="{{ route('test_run_show_page', [$project->id, $testRun->id]) }}">
                                    <i class="bi bi-play-circle"></i> {{$testRun->title}}
                                </a>
                            </div>
                            <div>
                                <span class="text-muted" title="created at">{{ $testRun->created_at->format('d-m-Y') }}</span>
                            </div>
                        </div>

                        @if($testRun->testPlan && $testRun->testPlan->description)
                            <div class="card-text text-muted ps-3">
                                <span>{!! preg_replace(
                                    '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#',
                                    '<a href="$0" target="_blank">$0</a>',
                                    e($testRun->testPlan->description)
                                ) !!}</span>
                            </div>
                        @endif

                        <!-- Button for generating the report -->
                        <div class="p-2 text-end">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="generateReport({{ $project->id }}, {{ $testRun->id }})">
                                <i class="bi bi-file-earmark-text"></i> Generate Report
                            </button>
                        </div>

                        <div class="border-top p-2">
                            @include('test_run.chart')
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

                $(".test-run-item").each(function() {
                    let title = $(this).find('a.fs-4').text().toLowerCase();

                    // Check for match in card title
                    if (title.includes(searchTerm)) {
                        $(this).show(); // Show card if there's a match
                    } else {
                        $(this).hide(); // Hide card if no match
                    }
                });
            });
        });

        function generateReport(projectId, testRunId) {
            if (projectId && testRunId) {
                // Construct the URL for generating the report
                const url = `/project/${projectId}/test-run/${testRunId}/generate-report`;

                // Redirect to the URL to generate the report
                window.location.href = url;
            } else {
                console.error("Project ID or Test Run ID is missing");
            }
        }
    </script>

@endsection
