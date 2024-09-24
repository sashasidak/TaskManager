@extends('layout.base_layout')

@section('content')

@include('layout.sidebar_nav')

<div class="col">

   <div class="border-bottom my-3">
       <h3 class="page_title">
           Repositories

               <a class="mx-3" href="{{ route('repository_create_page', $project->id) }}">
                   <button type="button" class="btn btn-sm btn-primary">
                       <i class="bi bi-plus-lg"></i> Add New
                   </button>
               </a>

               <!-- Кнопка для открытия модального окна -->
               <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#copyTasksModal">
                   <i class="bi bi-arrow-right"></i> Copy Tasks
               </button>
       </h3>
   </div>

   <!-- Поле ввода для фильтрации -->
   <div style="margin-bottom: 10px;">
       <input type="text" id="filter-input" class="form-control" placeholder="Введите для фильтрации...">
   </div>

   <div class="row row-cols-3 g-3" id="repository-list">
       @foreach($repositories as $repository)
           <div class="col repository-item">
               <div class="base_block border h-100 shadow-sm rounded">

                   <div class="card-body">
                       <div>
                           <i class="bi bi-stack"></i>
                           <a class="fs-4" href="{{ route('repository_show_page', [$project->id, $repository->id]) }}">{{$repository->title}}</a>
                       </div>

                       @if($repository->description)
                           <div class="card-text text-muted">
                               <span>{!! preg_replace(
                                   '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#',
                                   '<a href="$0" target="_blank">$0</a>',
                                   e($repository->description)
                               ) !!}</span>
                           </div>
                       @endif
                   </div>

                   <div class="d-flex justify-content-end border-top p-2">
                       <span class="text-muted">
                           <b>{{ $repository->suitesCount() }}</b> Test Suites
                           | <b>{{ $repository->casesCount() }}</b> Test Cases
                           | <b>{{ $repository->automatedCasesCount() }}</b> Automated
                       </span>
                   </div>

               </div>
           </div>
       @endforeach
   </div>

   <!-- Модальное окно для копирования задач -->
   <div class="modal fade" id="copyTasksModal" tabindex="-1" aria-labelledby="copyTasksModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-xl"> <!-- Изменен класс на modal-xl для увеличенного размера -->
           <div class="modal-content rounded-4 shadow-lg">
               <div class="modal-header border-bottom-0">
                   <h5 class="modal-title" id="copyTasksModalLabel">Copy Tasks</h5>
                   <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <form id="copy-tasks-form" method="POST">
                   @csrf
                   <div class="modal-body">
                       <div class="row">
                           <!-- Левая половина -->
                           <div class="col-md-6">
                               <div class="mb-3">
                                   <label for="source-repository" class="form-label fw-bold">Source Repository</label>
                                   <select id="source-repository" name="source_repository" class="form-select" required>
                                       @foreach($repositories as $repository)
                                           <option value="{{ $repository->id }}">{{ $repository->title }}</option>
                                       @endforeach
                                   </select>
                               </div>
                               <div class="mb-3">
                                   <label for="source-task-filter" class="form-label">Filter Tasks</label>
                                   <input type="text" id="source-task-filter" class="form-control" placeholder="Type to filter tasks">
                               </div>
                               <div class="mb-3">
                                   <label for="suites" class="form-label fw-bold">Select Tasks to Copy</label>
                                   <div id="suites-container" class="tasks-container">
                                       <!-- Динамически добавляемые задачи и тест-кейсы -->
                                   </div>
                               </div>
                           </div>

                           <!-- Правая половина -->
                           <div class="col-md-6">
                               <div class="mb-3">
                                   <label for="target-repository" class="form-label fw-bold">Target Repository</label>
                                   <select id="target-repository" name="target_repository" class="form-select" required>
                                       @foreach($repositories as $repository)
                                           <option value="{{ $repository->id }}">{{ $repository->title }}</option>
                                       @endforeach
                                   </select>
                               </div>
                               <div class="mb-3">
                                   <label for="target-task-filter" class="form-label">Filter Tasks</label>
                                   <input type="text" id="target-task-filter" class="form-control" placeholder="Type to filter tasks">
                               </div>
                               <div class="mb-3">
                                   <label for="target-suites" class="form-label fw-bold">Target Tasks</label>
                                   <div id="target-suites-container" class="tasks-container">
                                       <!-- Динамически добавляемые задачи для целевого репозитория -->
                                   </div>
                               </div>
                           </div>
                       </div>
                   </div>
                   <div class="modal-footer border-top-0 d-flex justify-content-between">
                       <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                       <button type="submit" class="btn btn-primary">Copy Tasks</button>
                   </div>
               </form>
           </div>
       </div>
   </div>

</div>

@endsection

@section('footer')
<style>
    .tasks-container {
        max-height: 300px;
        overflow-y: auto;
    }
    .task-item {
        border-radius: 8px;
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border: 1px solid #dee2e6;
        padding: 10px;
        margin-bottom: 10px;
    }
    .task-title {
        font-weight: bold;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        background: #ffffff;
        border-radius: 4px;
        padding: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .test-case {
        background: #f8f9fa;
        border-radius: 4px;
        padding: 8px;
        margin-top: 5px;
        border-left: 4px solid #007bff;
            margin-left: 20px; /* Добавлено для сдвига вправо */
    }
    .task-title .btn {
        margin-left: 10px;
    }
    .sub-task {
        background: #ffffff;
        border-radius: 4px;
        padding: 8px;
        margin-top: 5px;
        border-left: 4px solid #28a745;
    }
    .form-check-input {
        margin-right: 5px;
    }
    .form-check-label {
        display: inline;
    }
    .task-title input[type="checkbox"] {
        margin-right: 10px;
    }
</style>
<script>
   document.addEventListener('DOMContentLoaded', function () {
       // Функция для триггера события change
       function triggerChangeEvent(elementId) {
           const element = document.getElementById(elementId);
           if (element) {
               const event = new Event('change');
               element.dispatchEvent(event);
           }
       }
       // Функция фильтрации задач
           function filterTasks(inputId, containerId) {
               const filterValue = document.getElementById(inputId).value.toLowerCase();
               const tasksContainer = document.getElementById(containerId);
               const taskItems = tasksContainer.querySelectorAll('.task-item');

               taskItems.forEach(item => {
                   const title = item.querySelector('.task-title').textContent.toLowerCase();
                   if (title.includes(filterValue)) {
                       item.style.display = '';
                   } else {
                       item.style.display = 'none';
                   }
               });
           }

           // Обработчики событий для фильтрации задач
           document.getElementById('source-task-filter').addEventListener('input', function () {
               filterTasks('source-task-filter', 'suites-container');
           });

           document.getElementById('target-task-filter').addEventListener('input', function () {
               filterTasks('target-task-filter', 'target-suites-container');
           });

       // Обработчик для открытия модального окна
       document.getElementById('copyTasksModal').addEventListener('show.bs.modal', function () {
           triggerChangeEvent('source-repository');
           triggerChangeEvent('target-repository');
       });

       // Существующие обработчики
       document.getElementById('filter-input').addEventListener('input', function () {
           let searchTerm = this.value.toLowerCase();
           let repositoryItems = document.querySelectorAll('.repository-item');

           repositoryItems.forEach(function (item) {
               let repositoryTitle = item.querySelector('a.fs-4').textContent.toLowerCase();
               let repositoryDescription = item.querySelector('.card-text span')?.textContent.toLowerCase() || '';

               if (repositoryTitle.includes(searchTerm) || repositoryDescription.includes(searchTerm)) {
                   item.style.display = '';
               } else {
                   item.style.display = 'none';
               }
           });
       });

       document.getElementById('source-repository').addEventListener('change', function () {
           const sourceRepoId = this.value;
           const suitesContainer = document.getElementById('suites-container');
           suitesContainer.innerHTML = ''; // Очистить контейнер

           if (sourceRepoId) {
               fetch(`/repositories/${sourceRepoId}/suites`)
                   .then(response => response.json())
                   .then(data => {
                       // Функция для создания иерархии задач
                       const buildHierarchy = (suites) => {
                           const map = {};
                           const roots = [];

                           // Создаем карту задач по ID
                           suites.forEach(suite => {
                               map[suite.id] = { ...suite, children: [] };
                           });

                           // Определяем корневые задачи и подзадачи
                           suites.forEach(suite => {
                               if (suite.parent_id === null || suite.parent_id === undefined) {
                                   roots.push(map[suite.id]);
                               } else if (map[suite.parent_id]) {
                                   map[suite.parent_id].children.push(map[suite.id]);
                               }
                           });

                           return roots;
                       };

                       // Функция для создания элементов UI
                       const createSuiteElement = (suite, level = 0) => {
                           const container = document.createElement('div');
                           container.classList.add('task-item');

                           const title = document.createElement('div');
                           title.classList.add('task-title');
                           title.style.paddingLeft = `${level * 20}px`; // Отступ для визуализации уровня вложенности

                           // Чекбокс для задач, которые не являются подзадачами
                           if (suite.parent_id === null || suite.parent_id === undefined) {
                               const suiteCheckbox = document.createElement('input');
                               suiteCheckbox.type = 'checkbox';
                               suiteCheckbox.name = 'suite_ids[]'; // Массив ID задач
                               suiteCheckbox.value = suite.id;
                               suiteCheckbox.id = `suite-${suite.id}`;
                               suiteCheckbox.classList.add('form-check-input');

                               const suiteLabel = document.createElement('label');
                               suiteLabel.htmlFor = `suite-${suite.id}`;
                               suiteLabel.textContent = suite.title;
                               suiteLabel.classList.add('form-check-label');

                               title.appendChild(suiteCheckbox);
                               title.appendChild(suiteLabel);
                           } else {
                               const suiteLabel = document.createElement('label');
                               suiteLabel.textContent = suite.title;
                               suiteLabel.classList.add('form-check-label');
                               title.appendChild(suiteLabel);
                           }

                           // Иконка "Select All"
                           const selectAllIcon = document.createElement('button');
                           selectAllIcon.innerHTML = '<i class="bi bi-check-all"></i>'; // Используем иконку Bootstrap Icons
                           selectAllIcon.classList.add('btn', 'btn-sm', 'btn-primary', 'ms-3', 'select-all-icon');
                           selectAllIcon.type = 'button'; // Убедитесь, что кнопка не вызывает отправку формы
                           selectAllIcon.addEventListener('click', () => {
                               selectAllTestCases(testCaseContainer);
                           });
                           title.appendChild(selectAllIcon);
                           container.appendChild(title);

                           // Создание контейнера для тест-кейсов
                           const testCaseContainer = document.createElement('div');
                           testCaseContainer.classList.add('test-case');

                           fetch(`/suites/${suite.id}/test-cases`)
                               .then(response => response.json())
                               .then(testCasesData => {
                                   testCasesData.test_cases.forEach(testCase => {
                                       const testCaseDiv = document.createElement('div');
                                       testCaseDiv.classList.add('form-check');

                                       const testCaseCheckbox = document.createElement('input');
                                       testCaseCheckbox.type = 'checkbox';
                                       testCaseCheckbox.name = 'test_case_ids[]'; // Массив ID тест-кейсов
                                       testCaseCheckbox.value = testCase.id;
                                       testCaseCheckbox.id = `testcase-${testCase.id}`;
                                       testCaseCheckbox.classList.add('form-check-input', 'me-2');

                                       const testCaseLabel = document.createElement('label');
                                       testCaseLabel.htmlFor = `testcase-${testCase.id}`;
                                       testCaseLabel.textContent = testCase.title;
                                       testCaseLabel.classList.add('form-check-label');

                                       testCaseDiv.appendChild(testCaseCheckbox);
                                       testCaseDiv.appendChild(testCaseLabel);

                                       testCaseContainer.appendChild(testCaseDiv);
                                   });
                               })
                               .catch(error => {
                                   console.error('Error fetching test cases:', error);
                               });

                           container.appendChild(testCaseContainer);

                           // Создание контейнера для подзадач
                           if (suite.children && suite.children.length > 0) {
                               const childList = document.createElement('div');
                               childList.classList.add('sub-task');

                               // Создание подзадач в правильном порядке
                               suite.children.forEach(childSuite => {
                                   const childItem = createSuiteElement(childSuite, level + 1);
                                   childList.appendChild(childItem);
                               });

                               container.appendChild(childList);
                           }

                           return container;
                       };

                       const selectAllTestCases = (container) => {
                           const checkboxes = container.querySelectorAll('.form-check input[type="checkbox"]');
                           checkboxes.forEach(checkbox => checkbox.checked = true);
                       };

                       // Построение и отображение всех задач
                       const hierarchicalData = buildHierarchy(data.suites);
                       hierarchicalData.forEach(suite => {
                           const suiteElement = createSuiteElement(suite);
                           suitesContainer.appendChild(suiteElement);
                       });
                   })
                   .catch(error => {
                       console.error('Error fetching suites:', error);
                   });
           }
       });

       document.getElementById('target-repository').addEventListener('change', function () {
           const targetRepoId = this.value;
           const targetSuitesContainer = document.getElementById('target-suites-container');
           targetSuitesContainer.innerHTML = ''; // Очистить контейнер

           if (targetRepoId) {
               fetch(`/repositories/${targetRepoId}/suites`)
                   .then(response => response.json())
                   .then(data => {
                       // Функция для создания иерархии задач
                       const buildHierarchy = (suites) => {
                           const map = {};
                           const roots = [];

                           // Создаем карту задач по ID
                           suites.forEach(suite => {
                               map[suite.id] = { ...suite, children: [] };
                           });

                           // Определяем корневые задачи и подзадачи
                           suites.forEach(suite => {
                               if (suite.parent_id === null || suite.parent_id === undefined) {
                                   roots.push(map[suite.id]);
                               } else if (map[suite.parent_id]) {
                                   map[suite.parent_id].children.push(map[suite.id]);
                               }
                           });

                           return roots;
                       };

                       // Функция для создания элементов UI
                       const createSuiteElement = (suite, level = 0) => {
                           const container = document.createElement('div');
                           container.classList.add('task-item');

                           const title = document.createElement('div');
                           title.classList.add('task-title');
                           title.style.paddingLeft = `${level * 20}px`; // Отступ для визуализации уровня вложенности

                           // Основная радиокнопка задачи
                           const suiteRadio = document.createElement('input');
                           suiteRadio.type = 'radio';
                           suiteRadio.name = 'target_suite_id'; // Один ID задачи для целевого репозитория
                           suiteRadio.value = suite.id;
                           suiteRadio.id = `target-suite-${suite.id}`;
                           suiteRadio.classList.add('form-check-input');

                           const suiteLabel = document.createElement('label');
                           suiteLabel.htmlFor = `target-suite-${suite.id}`;
                           suiteLabel.textContent = suite.title;
                           suiteLabel.classList.add('form-check-label');

                           title.appendChild(suiteRadio);
                           title.appendChild(suiteLabel);
                           container.appendChild(title);

                           // Создание контейнера для подзадач
                           if (suite.children && suite.children.length > 0) {
                               const childList = document.createElement('div');
                               childList.classList.add('sub-task');

                               // Создание подзадач в правильном порядке
                               suite.children.forEach(childSuite => {
                                   const childItem = createSuiteElement(childSuite, level + 1);
                                   childList.appendChild(childItem);
                               });

                               container.appendChild(childList);
                           }

                           return container;
                       };

                       // Построение и отображение всех задач
                       const hierarchicalData = buildHierarchy(data.suites);
                       hierarchicalData.forEach(suite => {
                           const suiteElement = createSuiteElement(suite);
                           targetSuitesContainer.appendChild(suiteElement);
                       });
                   })
                   .catch(error => {
                       console.error('Error fetching target suites:', error);
                   });
           }
       });

       document.getElementById('copy-tasks-form').addEventListener('submit', function (e) {
           e.preventDefault(); // Предотвращает стандартное действие формы

           const sourceRepoId = document.getElementById('source-repository').value;
           const targetRepoId = document.getElementById('target-repository').value;

           // Получаем данные формы
           const formData = new FormData(this);

           // Устанавливаем URL для запроса
           const url = `{{ url('/copy-tasks') }}/${sourceRepoId}/${targetRepoId}`;

           // Отправка AJAX-запроса
           fetch(url, {
               method: 'POST',
               body: formData,
               headers: {
                   'X-Requested-With': 'XMLHttpRequest', // Указывает, что запрос сделан через AJAX
                   'X-CSRF-TOKEN': '{{ csrf_token() }}' // Добавляет CSRF токен для защиты
               }
           })
           .then(response => {
               if (!response.ok) {
                   // Если статус ответа не 2xx, выбрасываем ошибку
                   return response.json().then(data => Promise.reject(data));
               }
               return response.json();
           })
           .then(data => {
               if (data.message) {
                   toastr.success(data.message); // Успешное уведомление
                   const modal = bootstrap.Modal.getInstance(document.getElementById('copyTasksModal'));
                   if (modal) {
                       modal.hide(); // Закрытие модального окна
                   }
                   // Обновляем страницу после успешного копирования
                   location.reload();
               }
           })
           .catch(error => {
               if (error.message) {
                   toastr.error(error.message); // Уведомление об ошибке
               } else {
                   toastr.error('An error occurred. Please try again.'); // Общая ошибка
               }
               console.error('Error:', error);
           });
       });
   });
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
@endsection
