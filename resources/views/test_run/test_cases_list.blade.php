<div class="tree_suite">

    @foreach($suites as $testSuite)

        {{-- SHOW CHILD SUITE TITLE WITH FULL PATH --}}
        <div class="suite_header" style="background: #7c879138; padding-left: 5px; padding-bottom: 5px; border: 1px solid lightgray; border-radius: 3px; position: relative;">
            <i class="bi bi-folder2 fs-5"></i>

            <span class="text-muted" style="font-size: 14px">
                @foreach($testSuite->ancestors()->get()->reverse() as $parent)
                    {{$parent->title}}
                    <i class="bi bi-arrow-right-short"></i>
                @endforeach
            </span>
            <span class="suite_title" data-title="{{$testSuite->title}}" style="padding-right: 70px;">{{$testSuite->title}}</span>

            {{-- PDF Report Button (only for top-level suites) --}}
            @if($testSuite->parent_id === null)  <!-- Adjust condition based on your logic -->
            <button class="pdf-button" onclick="generatePdfReport({{$project->id}}, {{$testRun->id}}, {{$testSuite->id}})" style="position: absolute; right: 50px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;">
                <i class="bi bi-file-earmark-pdf-fill"></i>
            </button>
            @endif

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
                                <!-- New field for smartphone data -->
                                <div class="mb-3">
                                    <label for="smartphoneData" class="form-label">Smartphone Data</label>
                                    <input type="text" id="smartphoneData" class="form-control" placeholder="Enter smartphone data...">
                                </div>

                                <!-- Existing field for comment -->
                                <div class="mb-3">
                                    <label for="comment" class="form-label">Comment</label>
                                    <textarea id="comment" class="form-control" rows="4" placeholder="Enter your comment here..."></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="submitPdfReport()">Generate PDF</button>
                        </div>
                    </div>
                </div>
            </div>


            {{-- Collapse/Expand Button --}}
            <button class="toggle-button" onclick="toggleTestCases(this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;">
                <i class="bi bi-chevron-down"></i>
            </button>
        </div>

        <div class="tree_suite_test_cases">
            @foreach($testSuite->testCases->sortBy('order') as $testCase)
                @if(in_array($testCase->id, $testCasesIds))

                    <div class="tree_test_case tree_test_case_content py-1 ps-1" onclick="loadTestCase({{$testRun->id}}, {{$testCase->id}})">
                        <div class='d-flex justify-content-between'>
                            <div class="mt-1">
                                <span>@if($testCase->automated) <i class="bi bi-robot"></i> @else <i class="bi bi-person"></i> @endif </span>
                                <span class="text-muted ps-1 pe-3 ">{{$repository->prefix}}-{{$testCase->id}}</span>
                                <span class="test_case_title">{{$testCase->title}}</span>
                            </div>
                            <div class="result_badge pe-2" data-test_case_id="{{$testCase->id}}">
                                @if(isset($results[$testCase->id]))
                                    @if($results[$testCase->id] == \App\Enums\TestRunCaseStatus::NOT_TESTED)
                                        <span class="badge bg-secondary">Not Tested</span>
                                    @elseif($results[$testCase->id] == \App\Enums\TestRunCaseStatus::PASSED)
                                        <span class="badge bg-success">Passed</span>
                                    @elseif($results[$testCase->id] == \App\Enums\TestRunCaseStatus::FAILED)
                                        <span class="badge bg-danger">Failed</span>
                                    @elseif($results[$testCase->id] == \App\Enums\TestRunCaseStatus::BLOCKED)
                                        <span class="badge bg-warning">Blocked</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Not Tested</span>
                                @endif
                            </div>
                        </div>
                    </div>

                @endif
            @endforeach
        </div>

    @endforeach

</div>

<script>
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

    document.addEventListener('DOMContentLoaded', function() {
        // Получаем модальное окно
        var pdfReportModal = document.getElementById('pdfReportModal');

        // Создаем экземпляр модального окна Bootstrap
        var modal = new bootstrap.Modal(pdfReportModal);

        // Добавляем обработчик события для очистки текста при закрытии модального окна
        pdfReportModal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('comment').value = '';
        });
    });

    function generatePdfReport(projectId, testRunId, suiteId) {
        if (projectId && testRunId && suiteId) {
            // Показываем модальное окно
            var modal = new bootstrap.Modal(document.getElementById('pdfReportModal'));
            modal.show();

            // Сохраняем необходимые данные в форме
            document.getElementById('pdfReportForm').dataset.projectId = projectId;
            document.getElementById('pdfReportForm').dataset.testRunId = testRunId;
            document.getElementById('pdfReportForm').dataset.suiteId = suiteId;
        } else {
            console.error("Project ID, Test Run ID, or Suite ID is missing");
        }
    }

    function submitPdfReport() {
        const form = document.getElementById('pdfReportForm');
        const comment = document.getElementById('comment').value;
        const smartphoneData = document.getElementById('smartphoneData').value;
        const projectId = form.dataset.projectId;
        const testRunId = form.dataset.testRunId;
        const suiteId = form.dataset.suiteId;

        if (comment.trim() === '' || smartphoneData.trim() === '') {
            alert('Please fill in all fields.');
            return;
        }

        // Формируем URL с комментарием и данными о смартфоне
        const url = `/project/${projectId}/test-run/${testRunId}/generate-pdf/${suiteId}?comment=${encodeURIComponent(comment)}&smartphoneData=${encodeURIComponent(smartphoneData)}`;

        // Закрываем модальное окно
        var modal = bootstrap.Modal.getInstance(document.getElementById('pdfReportModal'));
        modal.hide();

        // Перенаправляем на URL
        window.location.href = url;
    }


    function shortenUrls(text) {
        const urlPattern = /(\b(https?|ftp|file):\/\/jira\.ab\.loc\/browse\/(\w+-\d+))/gi;
        return text.replace(urlPattern, (match, fullUrl, protocol, shortUrl) => {
            const shortenedText = shortUrl; // Например, A24MOB-33433
            return `<a href="${fullUrl}" class="branch-link" target="_blank">${shortenedText}</a>`;
        });
    }

    function updateSuiteTitles() {
        document.querySelectorAll('.suite_title').forEach(span => {
            const originalTitle = span.getAttribute('data-title');
            span.innerHTML = shortenUrls(originalTitle);
        });
    }

    document.addEventListener('DOMContentLoaded', updateSuiteTitles);
</script>

<style>
    .toggle-button i, .pdf-button i {
        font-size: 16px;
        color: darkgray;
    }

    .suite_header {
        padding-right: 80px; /* Добавляем пространство справа для кнопок */
    }

    .pdf-button {
        right: 50px; /* Положение кнопки PDF */
    }
    .modal-body .form-label {
        font-weight: bold;
    }

    .modal-body .form-control {
        margin-bottom: 15px;
    }

    .toggle-button {
        right: 10px; /* Положение кнопки Collapse/Expand */
    }
</style>
