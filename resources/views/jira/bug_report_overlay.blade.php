<!-- components/overlay.blade.php -->
<div class="overlay" style="display: none;">
    <div class="overlay-content">
        <!-- Дропбокс с выбором типа ошибки (слева) -->
        <div class="form-group d-flex justify-content-between align-items-center mb-4">
            <select id="errorType" name="error_type" class="form-control error-type-select">
                  <option value="bug_fix">Исправление ошибки</option>
                  <option value="design_issue">Ошибка дизайна</option>
            </select>
            <h2 class="ml-3">Сообщить об ошибке</h2>
            <button type="button" class="close-overlay btn btn-secondary ml-auto">Закрыть</button>
        </div>

        <form action="{{ route('jira.createBugReport') }}" method="POST">
            @csrf

            <!-- Скрытые поля -->
            <input type="hidden" id="issueKey" name="issue_key">
            <input type="hidden" id="customerKey" name="customer_key">
            <input type="hidden" id="executorKey" name="executor_key">
            <input type="hidden" id="errorTypeHidden" name="error_type">

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

            <!-- Кнопка для отправки формы -->
            <button type="submit" class="btn btn-primary">Отправить отчет</button>
        </form>
    </div>
</div>

<style>
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

.overlay-content h2 {
    margin: 0 auto;
    flex-grow: 1;
    text-align: center;
    margin-left: -150px;
    z-index: 1;
}

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
}

.error-type-select {
    width: 150px !important;
    flex-shrink: 0;
    z-index: 2;
}

.platform-select,
.severity-select {
    flex: 0 1 auto; /* Делаем так, чтобы элементы могли сжиматься, но не расширяться */
    margin-right: 5px; /* Минимальный отступ между элементами */
}

.form-row {
    display: flex;
    align-items: center; /* Центрируем элементы по вертикали */
    gap: 0; /* Убираем промежутки между элементами */
}

.form-group {
    margin-bottom: 0; /* Убираем нижние отступы у групп */
}

.platform-group,
.severity-group {
    flex: 0 1 auto; /* Делаем элементы гибкими, чтобы занимали только необходимое пространство */
}

.subject-group {
    flex: 1; /* Поле "Тема" будет занимать оставшееся пространство */
    margin-left: 5px; /* Небольшой отступ слева для отделения от предыдущих элементов */
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
});
</script>
