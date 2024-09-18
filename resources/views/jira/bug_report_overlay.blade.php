<!-- components/overlay.blade.php -->
<div class="overlay" style="display: none;">
@section('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link href="{{ asset('editor/summernote-lite.min.css') }}" rel="stylesheet">
    <script src="{{ asset('editor/summernote-lite.min.js') }}"></script>
@endsection
    <div class="overlay-content">
        <!-- Дропбокс с выбором типа ошибки (слева) -->
        <div class="form-group d-flex justify-content-between align-items-center mb-4">
            <h2 class="ml-3">Сообщить об ошибке</h2>
            <button type="button" class="close-overlay btn btn-secondary ml-auto">Закрыть</button>
        </div>

        <form id="bugReportForm" action="{{ route('jira.createBugReport') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Скрытые поля -->
            <input type="hidden" id="issueKey" name="issue_key">
            <input type="hidden" id="customerKey" name="customer_key">
            <input type="hidden" id="executorKey" name="executor_key">

            <!-- Поле для выбора типа ошибки -->
            <select id="errorType" name="error_type" class="form-control error-type-select mb-3">
                <option value="bug_fix">Исправление ошибки</option>
                <option value="design_issue">Ошибка дизайна</option>
            </select>

            <!-- Платформа, Серьезность и Тема в одной строке -->
            <div class="form-row d-flex justify-content-between mb-3">
                <div class="form-group col-md-2 platform-group">
                    <label for="platform">Платформа</label>
                    <select id="platform" name="platform" class="form-control platform-select">
                        <option value="AOS">AOS</option>
                        <option value="IOS">IOS</option>
                        <option value="Back">Back</option>
                    </select>
                </div>
                <div class="form-group col-md-2 severity-group">
                    <label for="severity">Серьезность</label>
                    <select id="severity" name="severity" class="form-control severity-select">
                        <option value="10200">S1</option>
                        <option value="10201">S2</option>
                        <option value="10202">S3</option>
                        <option value="10203">S4</option>
                        <option value="10204">S5</option>
                        <option value="-1">Не выбрано</option>
                    </select>
                </div>
                <div class="form-group col-md-8 subject-group">
                    <label for="subject">Тема</label>
                    <input type="text" id="subject" name="subject" class="form-control" placeholder="Введите тему" required>
                </div>
            </div>

            <!-- Поле для шагов -->
            <div class="form-group">
                <label for="steps">Шаги</label>
                <textarea id="steps" name="steps" class="form-control" placeholder="Опишите шаги, которые привели к ошибке" rows="4" required></textarea>
            </div>

            <!-- Поле для фактического результата -->
            <div class="form-group">
                <label for="actual_result">Фактический результат</label>
                <textarea id="actual_result" name="actual_result" class="form-control" placeholder="Опишите фактический результат" rows="3" required></textarea>
            </div>

            <!-- Поле для ожидаемого результата -->
            <div class="form-group">
                <label for="expected_result">Ожидаемый результат</label>
                <textarea id="expected_result" name="expected_result" class="form-control" placeholder="Опишите ожидаемый результат" rows="3" required></textarea>
            </div>

            <!-- Поле для устройства -->
            <div class="form-group">
                <label for="device">Устройство</label>
                <input type="text" id="device" name="device" class="form-control" placeholder="Введите устройство" required>
            </div>

            <!-- Поле для исполнителя -->
            <div class="form-group">
                <label for="executor">Исполнитель</label>
                <input type="text" id="executor" name="executor" class="form-control" placeholder="Введите имя исполнителя" autocomplete="off">
                <ul id="executor-list" class="list-group" style="display: none;"></ul>
            </div>

            <!-- Поле для заказчика -->
            <div class="form-group">
                <label for="customer">Заказчик</label>
                <input type="text" id="customer" name="customer" class="form-control" placeholder="Введите имя заказчика" autocomplete="off">
                <ul id="customer-list" class="list-group" style="display: none;"></ul>
            </div>

            <!-- Поле для прикрепления файлов -->
            <div class="form-group">
                <input type="file" id="attachments" name="attachments[]" multiple class="form-control-file">
            </div>

            <!-- Кнопка для отправки формы -->
            <button type="submit" class="btn btn-primary">Отправить отчет</button>
        </form>
    </div>
</div>

<style>
/* Общие стили для оверлея */
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

/* Контент оверлея */
.overlay-content {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    max-width: 800px;
    width: 100%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    position: relative;
    transform: translate(450px, 50px);
}

/* Заголовки */
.overlay-content h2 {
    text-align: left;
    margin: 0;
    font-size: 24px; /* Размер шрифта заголовка */
    font-weight: 600; /* Толщина шрифта */
    color: #007bff; /* Цвет заголовка */
    margin-bottom: 20px; /* Отступ снизу */
}

/* Кнопка закрытия */
.close-overlay {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #f44336;
    color: #fff;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    z-index: 999;
    font-size: 16px; /* Размер шрифта */
}

/* Поля выбора */
.error-type-select,
.platform-select,
.severity-select {
    font-size: 14px; /* Размер шрифта */
    font-family: 'Arial', sans-serif; /* Шрифт для полей выбора */
}

/* Строка формы */
.form-row {
    display: flex;
    align-items: center;
    gap: 0;
}

/* Группы формы */
.form-group {
    margin-bottom: 1rem;
}

.platform-group,
.severity-group {
    flex: 0 1 auto;
}

.subject-group {
    flex: 1;
    margin-left: 5px;
}

/* Метки и поля ввода */
.form-group label {
    display: block;
    text-align: left;
    font-size: 16px; /* Размер шрифта меток */
    font-weight: 500; /* Толщина шрифта */
    color: #333; /* Цвет меток */
    margin-bottom: 5px; /* Отступ снизу */
}

.form-control {
    width: 100%;
    margin-bottom: 0.5rem;
    font-size: 14px; /* Размер шрифта полей ввода */
    font-family: 'Arial', sans-serif; /* Шрифт для полей ввода */
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    padding: 0.375rem 0.75rem;
}

/* Поле для файлов */
.form-control-file {
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    padding: 0.375rem 0.75rem;
    font-size: 14px; /* Размер шрифта для поля файлов */
    font-family: 'Arial', sans-serif; /* Шрифт для поля файлов */
}

/* Кнопка отправки */
.btn-primary {
    font-size: 16px; /* Размер шрифта кнопки */
    font-weight: 600; /* Толщина шрифта кнопки */
    padding: 10px 20px; /* Отступы кнопки */
    border-radius: 4px; /* Скругление углов кнопки */
}


</style>

<script>
$(document).ready(function() {
    let currentQueryCustomer = ''; // Хранение текущего запроса для заказчика
    let currentQueryExecutor = ''; // Хранение текущего запроса для исполнителя
    let currentRequestCustomer = null; // Хранение текущего запроса Ajax для заказчика
    let currentRequestExecutor = null; // Хранение текущего запроса Ajax для исполнителя
    // Загрузка данных из Local Storage
    function loadFormData() {
        const formData = JSON.parse(localStorage.getItem('overlayFormData')) || {};
        $('#platform').val(formData.platform || '');
        $('#severity').val(formData.severity || '');
        $('#subject').val(formData.subject || '');
        $('#steps').val(formData.steps || '');
        $('#actual_result').val(formData.actual_result || '');
        $('#expected_result').val(formData.expected_result || '');
        $('#device').val(formData.device || '');
        $('#executor').val(formData.executor || '');
        $('#customer').val(formData.customer || '');
        $('#errorType').val(formData.error_type || '');
        if (formData.executorKey) {
            $('#executorKey').val(formData.executorKey);
        }
        if (formData.customerKey) {
            $('#customerKey').val(formData.customerKey);
        }
    }
    // Сохранение данных в Local Storage
    function saveFormData() {
        const formData = {
            platform: $('#platform').val(),
            severity: $('#severity').val(),
            subject: $('#subject').val(),
            steps: $('#steps').val(),
            actual_result: $('#actual_result').val(),
            expected_result: $('#expected_result').val(),
            device: $('#device').val(),
            executor: $('#executor').val(),
            customer: $('#customer').val(),
            error_type: $('#errorType').val(),
            executorKey: $('#executorKey').val(),
            customerKey: $('#customerKey').val(),
        };
        localStorage.setItem('overlayFormData', JSON.stringify(formData));
    }
    // Функция для создания дебаунса
    function debounce(func, delay) {
        let timer;
        return function(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => {
                func.apply(this, args);
            }, delay);
        };
    }
    // Обработчик события изменения полей формы с сохранением в Local Storage
    const debouncedSave = debounce(saveFormData, 300);
    $('#platform, #severity, #subject, #steps, #actual_result, #expected_result, #device, #executor, #customer, #errorType').on('input change', debouncedSave);
    // Применение дебаунса для ввода в поле заказчика
    $('#customer').on('input', debounce(searchCustomer, 300));
    // Применение дебаунса для ввода в поле исполнителя
    $('#executor').on('input', debounce(searchExecutor, 300));
    // Загрузка данных при загрузке страницы
    loadFormData();
    // Функция для поиска заказчика
    function searchCustomer() {
        var query = $('#customer').val();
        // Не отправляем запрос, если длина запроса меньше 3 символов
        if (query.length < 3) {
            $('#customer-list').hide();
            return;
        }
        // Если запрос не изменился, не отправляем новый запрос
        if (query === currentQueryCustomer) {
            return;
        }
        currentQueryCustomer = query; // Обновляем текущий запрос
        // Если предыдущий запрос ещё выполняется, отменяем его
        if (currentRequestCustomer) {
            currentRequestCustomer.abort();
        }
        currentRequestCustomer = $.ajax({
            url: "{{ route('jira.searchCustomer') }}",
            method: 'GET',
            data: { query: query },
            success: function(response) {
                $('#customer-list').empty().show();
                response.forEach(function(user) {
                    var displayName = user.displayName ? user.displayName : 'Имя не указано';
                    var avatarUrl = user.avatarUrls && user.avatarUrls['16x16'] ? user.avatarUrls['16x16'] : '';
                    $('#customer-list').append(
                        '<li class="list-group-item" data-key="' + user.key + '">' +
                        (avatarUrl ? '<img src="' + avatarUrl + '" alt="Avatar" class="rounded-circle mr-2" style="width: 16px; height: 16px;">' : '') +
                        displayName +
                        '</li>'
                    );
                });
            },
            error: function() {
                $('#customer-list').hide();
                alert('Ошибка при поиске заказчика.');
            },
            complete: function() {
                currentRequestCustomer = null; // Сброс текущего запроса
            }
        });
    }
    // Функция для поиска исполнителя
    function searchExecutor() {
        var query = $('#executor').val();
        // Не отправляем запрос, если длина запроса меньше 3 символов
        if (query.length < 3) {
            $('#executor-list').hide();
            return;
        }
        // Если запрос не изменился, не отправляем новый запрос
        if (query === currentQueryExecutor) {
            return;
        }
        currentQueryExecutor = query; // Обновляем текущий запрос
        // Если предыдущий запрос ещё выполняется, отменяем его
        if (currentRequestExecutor) {
            currentRequestExecutor.abort();
        }
        currentRequestExecutor = $.ajax({
            url: "{{ route('jira.searchExecutor') }}",
            method: 'GET',
            data: { query: query },
            success: function(response) {
                $('#executor-list').empty().show();
                response.forEach(function(user) {
                    var displayName = user.displayName ? user.displayName : 'Имя не указано';
                    var avatarUrl = user.avatarUrls && user.avatarUrls['16x16'] ? user.avatarUrls['16x16'] : '';
                    $('#executor-list').append(
                        '<li class="list-group-item" data-key="' + user.key + '">' +
                        (avatarUrl ? '<img src="' + avatarUrl + '" alt="Avatar" class="rounded-circle mr-2" style="width: 16px; height: 16px;">' : '') +
                        displayName +
                        '</li>'
                    );
                });
            },
            error: function() {
                $('#executor-list').hide();
                alert('Ошибка при поиске исполнителя.');
            },
            complete: function() {
                currentRequestExecutor = null; // Сброс текущего запроса
            }
        });
    }
    // Применение дебаунса для ввода в поле заказчика
    $('#customer').on('input', debounce(searchCustomer, 300));
    // Применение дебаунса для ввода в поле исполнителя
    $('#executor').on('input', debounce(searchExecutor, 300));
    // Функция для выбора заказчика из списка
    $(document).on('click', '#customer-list li', function() {
        var selectedCustomer = $(this).text();
        var customerKey = $(this).data('key'); // Получаем ключ заказчика
        $('#customer').val(selectedCustomer);
        $('#customerKey').val(customerKey); // Сохраняем ключ в скрытое поле
        $('#customer-list').hide();
    });
    // Функция для выбора исполнителя из списка
    $(document).on('click', '#executor-list li', function() {
        var selectedExecutor = $(this).text();
        var executorKey = $(this).data('key'); // Получаем ключ исполнителя
        $('#executor').val(selectedExecutor);
        $('#executorKey').val(executorKey); // Сохраняем ключ в скрытое поле
        $('#executor-list').hide();
    });
    // Обновление скрытого поля для типа ошибки при изменении значения в дропдауне
        $('#errorType').on('change', function() {
            $('#errorTypeHidden').val($(this).val());
        });
    // Функция для закрытия оверлея
    $(document).on('click', '.close-overlay', function() {
        $('.overlay').hide();
    });
    // Открытие оверлея
    $(document).on('click', '.open-overlay', function() {
        $('.overlay').show();
    });
    // Отображение выбранных файлов
            $('#files').on('change', function() {
                const files = $(this)[0].files;
                if (files.length) {
                    let fileNames = Array.from(files).map(file => file.name).join(', ');
                    alert('Вы выбрали файлы: ' + fileNames);
                }
            });
});
</script>
<script>
    $(document).ready(function() {
        $('#steps, #actual_result, #expected_result').summernote({
            minHeight: '50px',
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']]
            ],
            buttons: {
                code: function(context) {
                    var ui = $.summernote.ui;
                    var button = ui.button({
                        contents: '<i class="note-icon-code"/>',
                        tooltip: 'Insert Code',
                        click: function() {
                            context.invoke('editor.formatBlock', 'pre');
                        }
                    });
                    return button.render();
                }
            },
            callbacks: {
                onInit: function() {
                    // Применяем стиль выравнивания по левому краю при инициализации
                    $('.note-editable').css('text-align', 'left');
                }
            }
        });
    });
</script>



