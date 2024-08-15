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
                            <div class="card-text text-muted ps-3" id="description-{{ $testRun->id }}">
                                <span>{!! preg_replace(
                                    '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#',
                                    '<a href="$0" target="_blank">$0</a>',
                                    e($testRun->testPlan->description)
                                ) !!}</span>
                            </div>
                        @endif

                        <!-- Button for generating the report -->
                        <div class="p-2 text-end">
                            <button type="button" class="btn btn-sm btn-secondary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#pdfReportModal"
                                    data-project-id="{{ $project->id }}"
                                    data-test-run-id="{{ $testRun->id }}"
                                    data-description-id="description-{{ $testRun->id }}">
                                <i class="bi bi-file-earmark-text"></i> Generate Report
                            </button>
                        </div>

                        <!-- Modal for PDF Report -->
                        <div class="modal fade" id="pdfReportModal" tabindex="-1" aria-labelledby="pdfReportModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="pdfReportModalLabel">Enter Details for PDF Report</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="pdfReportForm">
                                            <!-- Hidden field for description -->
                                            <input type="hidden" id="hiddenDescription" name="description">

                                            <!-- Dropdown for report type -->
                                            <div class="mb-3">
                                                <label for="reportType" class="form-label">Report Type</label>
                                                <select id="reportType" class="form-select">
                                                    <option value="regress">Регресс</option>
                                                    <option value="smoke">Смоук</option>
                                                    <option value="simple">Просто</option>
                                                </select>
                                            </div>

                                            <!-- Field for smartphone data -->
                                            <div class="mb-3">
                                                <label for="smartphoneData" class="form-label">Smartphone Data</label>
                                                <input type="text" id="smartphoneData" class="form-control" placeholder="Enter smartphone data...">
                                            </div>

                                            <!-- Field for comment -->
                                            <div class="mb-3">
                                                <label for="comment" class="form-label">Comment</label>
                                                <textarea id="comment" class="form-control" rows="4" placeholder="Enter your comment here..."></textarea>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary" onclick="submitPdfReport(this)"
                                                data-project-id="{{ $project->id }}"
                                                data-test-run-id="{{ $testRun->id }}">
                                            Generate PDF
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-top p-2">
                        <!-- Progress Bar -->
                        <div class="progress" style="height: 20px;">
                            @php
                                $total = array_sum($testRunStatusCounts[$testRun->id]);
                                $widths = [];
                                if ($total > 0) {
                                    foreach (['passed', 'failed', 'blocked', 'not_tested'] as $status) {
                                        $widths[$status] = ($testRunStatusCounts[$testRun->id][$status] / $total) * 100;
                                    }
                                }
                            @endphp
                            <div class="progress-bar bg-success position-relative" role="progressbar" style="width: {{ $widths['passed'] ?? 0 }}%;">
                                <span class="text-white position-absolute w-100 text-center" style="font-size: 0.9rem;">{{ $testRunStatusCounts[$testRun->id]['passed'] }}</span>
                            </div>
                            <div class="progress-bar bg-danger position-relative" role="progressbar" style="width: {{ $widths['failed'] ?? 0 }}%;">
                                <span class="text-white position-absolute w-100 text-center" style="font-size: 0.9rem;">{{ $testRunStatusCounts[$testRun->id]['failed'] }}</span>
                            </div>
                            <div class="progress-bar bg-warning position-relative" role="progressbar" style="width: {{ $widths['blocked'] ?? 0 }}%;">
                                <span class="text-white position-absolute w-100 text-center" style="font-size: 0.9rem;">{{ $testRunStatusCounts[$testRun->id]['blocked'] }}</span>
                            </div>
                            <div class="progress-bar bg-secondary position-relative" role="progressbar" style="width: {{ $widths['not_tested'] ?? 0 }}%;">
                                <span class="text-white position-absolute w-100 text-center" style="font-size: 0.9rem;">{{ $testRunStatusCounts[$testRun->id]['not_tested'] }}</span>
                            </div>
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

            $('#pdfReportModal').on('show.bs.modal', function (event) {
                // Get the button that triggered the modal
                var button = $(event.relatedTarget);
                var descriptionId = button.data('description-id');

                // Get the description text
                var descriptionText = $('#' + descriptionId).text().trim();

                // Set the description in the hidden field
                $('#hiddenDescription').val(descriptionText);

                // Clear the form fields
                $('#pdfReportForm')[0].reset();
            });
        });

        function submitPdfReport(button) {
            const reportType = document.getElementById('reportType').value;
            const smartphoneData = document.getElementById('smartphoneData').value;
            const comment = document.getElementById('comment').value;
            const description = document.getElementById('hiddenDescription').value;
            const projectId = button.getAttribute('data-project-id');
            const testRunId = button.getAttribute('data-test-run-id');

            const url = `/project/${projectId}/test-run/${testRunId}/generate-report?reportType=${encodeURIComponent(reportType)}&smartphoneData=${encodeURIComponent(smartphoneData)}&comment=${encodeURIComponent(comment)}&description=${encodeURIComponent(description)}`;

            // Закрываем модальное окно
                var modal = bootstrap.Modal.getInstance(document.getElementById('pdfReportModal'));
                if (modal) {
                    modal.hide();
                }

            window.location.href = url;
        }
    </script>

@endsection
