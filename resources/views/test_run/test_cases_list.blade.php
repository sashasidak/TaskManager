<div class="tree_suite">
@section('head')
    <link href="{{ asset('editor/summernote-lite.min.css') }}" rel="stylesheet">
    <script src="{{ asset('editor/summernote-lite.min.js') }}"></script>
@endsection

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

         {{-- Bugreport Button (appears if Jira link is present) --}}
         <button class="bugreport-button" style="position: absolute; right: 90px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;">
             <i class="bi bi-bug"></i>
         </button>

         {{-- Подключаем overlay --}}
         @include('jira.bug_report_overlay')

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

                                <!-- Existing field for comment with maxlength -->
                                <div class="mb-3">
                                    <label for="comment" class="form-label">Comment</label>
                                    <textarea id="comment" class="form-control" rows="4" maxlength="1000" placeholder="Enter your comment here..."></textarea>
                                    <small class="form-text text-muted">Maximum 1000 characters</small>
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
            document.getElementById('smartphoneData').value = ''; // Очистка поля для данных смартфона
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

    function checkForJiraLinks() {
        // Сначала скрываем все кнопки багрепорта
        document.querySelectorAll('.bugreport-button').forEach(function(button) {
            button.style.display = 'none';
        });

        // Проверяем каждый элемент с классом '.suite_title'
        document.querySelectorAll('.suite_title').forEach(function(element) {
            let title = element.getAttribute('data-title');
            if (title && title.includes('jira.ab.loc')) {
                // Ищем ближайший элемент с классом '.suite_header'
                let suiteHeader = element.closest('.suite_header');
                if (suiteHeader) {
                    let bugReportButton = suiteHeader.querySelector('.bugreport-button');

                    // Если кнопка найдена, показываем ее
                    if (bugReportButton) {
                        bugReportButton.style.display = 'block';

                        // Ищем ключ задачи в ссылке
                        let jiraIssueKeyMatch = title.match(/http:\/\/jira\.ab\.loc\/browse\/(\w+-\d+)/);
                        if (jiraIssueKeyMatch) {
                            let issueKey = jiraIssueKeyMatch[1];

                            // Добавляем обработчик клика для кнопки багрепорта
                            bugReportButton.addEventListener('click', function() {
                                let issueKeyInput = document.querySelector('#issueKey');
                                issueKeyInput.value = issueKey;

                                // Открываем оверлей с формой багрепорта
                                document.querySelector('.overlay').style.display = 'block';
                            });
                        }
                    }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateSuiteTitles();
        checkForJiraLinks();
    });
</script>
<script>
            $( document ).ready(function() {

                $('#comment').summernote({
                    minHeight: '200px',
                });

            });
    </script>
<script>
 document.addEventListener('DOMContentLoaded', function() {
     document.querySelectorAll('.suite_title').forEach(function(element) {
         let title = element.getAttribute('data-title');
         let jiraIssueKeyMatch = title.match(/http:\/\/jira\.ab\.loc\/browse\/(\w+-\d+)/);

         if (jiraIssueKeyMatch) {
             let issueKey = jiraIssueKeyMatch[1];
             fetch(`/jira/issue-estimate/${issueKey}`)
                 .then(response => response.json())
                 .then(data => {
                     if (data.status === 200) {
                         let timeSpent = data.time_spent;
                         let originalEstimate = data.original_estimate;

                         // Создаем estimate-box
                         let estimateBox = document.createElement('div');
                         estimateBox.className = 'estimate-box';
                         estimateBox.textContent = `${timeSpent}/${originalEstimate}`;
                         element.appendChild(estimateBox);

                         // Создаем subtaskList-box
                         let subtaskListBox = document.createElement('div');
                         subtaskListBox.className = 'subtaskList-box';

                         // Создаем элемент для заголовка (полоски)
                         let toggleHeader = document.createElement('div');
                         toggleHeader.style.cursor = 'pointer'; // Курсор в виде руки
                         toggleHeader.style.backgroundColor = '#007bff'; // Цвет фона
                         toggleHeader.style.height = '5px'; // Высота полоски
                         toggleHeader.style.borderRadius = '2px'; // Скругление углов

                         // Создаем элемент списка подзадач, если они есть
                         if (data.subtasks.length > 0) {
                             let subtaskList = document.createElement('ul');
                             subtaskList.style.display = 'none'; // Изначально скрываем список
                             subtaskList.style.flexWrap = 'wrap'; // Разрешаем перенос на новую строку
                             subtaskList.style.listStyleType = 'none'; // Убираем маркеры списка
                             subtaskList.style.padding = '0'; // Убираем отступы
                             subtaskList.style.margin = '0'; // Убираем поля


                             data.subtasks.forEach(subtask => {
                                 let listItem = document.createElement('li');
                                 listItem.style.marginRight = '10px'; // Отступ между элементами

                                 // Создаем элемент для ссылки
                                 let keyElement = document.createElement('a');
                                 let keyId = subtask.key.split('-').pop(); // Оставляем только часть после дефиса
                                 keyElement.textContent = keyId; // Устанавливаем отображаемый текст
                                 keyElement.href = `http://jira.ab.loc/browse/${subtask.key}`; // Устанавливаем ссылку
                                 keyElement.style.fontSize = '0.8em'; // Уменьшаем размер шрифта
                                 keyElement.style.textDecoration = 'none'; // Убираем подчеркивание
                                 keyElement.style.color = 'blue'; // Задаем цвет ссылки
                                 keyElement.target = '_blank'; // Открывать в новой вкладке

                                 // Создаем элемент для статуса
                                 let statusDot = document.createElement('span');
                                 statusDot.style.display = 'inline-block';
                                 statusDot.style.width = '10px';
                                 statusDot.style.height = '10px';
                                 statusDot.style.borderRadius = '50%'; // Делает точку круглой
                                 statusDot.style.marginLeft = '5px';

                                 // Устанавливаем цвет точки в зависимости от статуса
                                 switch (subtask.status) {
                                     case 'WAIT:Test':
                                         statusDot.style.backgroundColor = 'blue'; // Синяя точка
                                         break;
                                     case 'PROCESS:TEST':
                                         statusDot.style.backgroundColor = 'green'; // Зеленая точка
                                         break;
                                     default:
                                         statusDot.style.backgroundColor = 'gray'; // Серая точка
                                         break;
                                 }

                                 // Добавляем элементы в listItem
                                 listItem.appendChild(keyElement);
                                 listItem.appendChild(statusDot);
                                 subtaskList.appendChild(listItem);
                             });

                             subtaskListBox.appendChild(toggleHeader);
                             subtaskListBox.appendChild(subtaskList);

                             // Обработчик события для сворачивания/разворачивания
                             toggleHeader.addEventListener('click', () => {
                                 if (subtaskList.style.display === 'none') {
                                     subtaskList.style.display = 'flex'; // Разворачиваем список
                                 } else {
                                     subtaskList.style.display = 'none'; // Сворачиваем список
                                 }
                             });
                         } else {}
                         element.appendChild(subtaskListBox);
                     } else {
                         console.error(`Error fetching estimate: ${data.error}`);
                     }
                 })
                 .catch(error => console.error('Fetch error:', error));
         }
     });
 });
 // Находим элементы
     const bugReportButton = document.querySelector('.bugreport-button');
     const overlay = document.querySelector('.overlay');
     const closeOverlayButton = document.querySelector('.close-overlay');

     // Показать overlay при нажатии на кнопку
     bugReportButton.addEventListener('click', () => {
         overlay.style.display = 'flex';
     });

     // Скрыть overlay при нажатии на кнопку закрытия
     closeOverlayButton.addEventListener('click', () => {
         overlay.style.display = 'none';
     });
</script>
<style>
    .toggle-button i, .pdf-button i, .bugreport-button i {
        font-size: 16px;
        color: darkgray;
    }

    .suite_header {
        padding-right: 50px; /* Добавляем пространство справа для кнопок */
        display: flex;
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
    .subtaskList-box {
        margin-top: 10px;
        padding: 5px;
        background-color: #f8f9fa; /* Светлый фон */
        border: 1px solid lightgray;
        border-radius: 4px;
    }
    .subtaskList-box ul {
        list-style-type: none; /* Убираем маркеры списка */
        padding-left: 0; /* Убираем отступ */
    }
    .subtaskList-box li {
        margin: 2px 0; /* Отступ между элементами списка */
    }
    .subtaskList-box div {
        margin-bottom: 0; /* Убираем отступ между полоской и списком */
    }
    .estimate-box {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            background-color: #e0f7fa; /* Светло-синий фон */
            color: #00796b; /* Тёмно-зелёный цвет текста */
            font-size: 14px;
            font-weight: bold;
            margin-left: 10px;
        }
        /* Стили для overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .overlay-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .close-overlay {
            background: none;
            border: 1px solid #ccc;
            padding: 5px 10px;
            cursor: pointer;
        }
</style>
