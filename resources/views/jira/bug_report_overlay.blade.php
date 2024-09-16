<!-- components/overlay.blade.php -->
<div class="overlay" style="display: none;">
    <div class="overlay-content">
        <h2>Сообщить об ошибке</h2>

        <form action="{{ route('jira.createBugReport') }}" method="POST">
            @csrf

            <!-- Скрытое поле для ключа Jira задачи -->
            <input type="hidden" id="issueKey" name="issue_key">

            <!-- Скрытое поле для ключа заказчика -->
            <input type="hidden" id="customerKey" name="customer_key">

            <!-- Скрытое поле для ключа исполнителя -->
            <input type="hidden" id="executorKey" name="executor_key">

            <!-- Поле для устройства -->
            <div class="form-group">
                <label for="device">Устройство</label>
                <input type="text" id="device" name="device" class="form-control" placeholder="Введите устройство" required>
            </div>

            <!-- Поле для заказчика (основной) -->
            <div class="form-group">
                <label for="customer">Заказчик (основной)</label>
                <input type="text" id="customer" name="customer" class="form-control" placeholder="Введите имя заказчика" autocomplete="off">
                <ul id="customer-list" class="list-group" style="display: none;"></ul>
            </div>

            <!-- Поле для исполнителя -->
            <div class="form-group">
                <label for="executor">Исполнитель</label>
                <input type="text" id="executor" name="executor" class="form-control" placeholder="Введите имя исполнителя" autocomplete="off">
                <ul id="executor-list" class="list-group" style="display: none;"></ul>
            </div>

            <!-- Поле для темы -->
            <div class="form-group">
                <label for="subject">Тема</label>
                <input type="text" id="subject" name="subject" class="form-control" placeholder="Введите тему" required>
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

            <!-- Поле для выбора серьезности -->
            <div class="form-group">
                <label for="severity">Серьезность</label>
                <select id="severity" name="severity" class="form-control" required>
                    <option value="">Выберите серьезность</option>
                    <option value="10200">S1</option>
                    <option value="10201">S2</option>
                    <option value="10202">S3</option>
                    <option value="10203">S4</option>
                    <option value="10204">S5</option>
                    <option value="-1">Не выбрано</option>
                </select>
            </div>

            <!-- Кнопка для отправки формы -->
            <button type="submit" class="btn btn-primary">Отправить отчет</button>

            <!-- Кнопка закрытия -->
            <button type="button" class="close-overlay btn btn-secondary">Закрыть</button>
        </form>
    </div>
</div>



<script>
$(document).ready(function() {
    let currentQueryCustomer = ''; // Хранение текущего запроса для заказчика
    let currentQueryExecutor = ''; // Хранение текущего запроса для исполнителя
    let currentRequestCustomer = null; // Хранение текущего запроса Ajax для заказчика
    let currentRequestExecutor = null; // Хранение текущего запроса Ajax для исполнителя

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

    // Функция для закрытия оверлея
    $(document).on('click', '.close-overlay', function() {
        $('.overlay').hide();
    });
});
</script>
