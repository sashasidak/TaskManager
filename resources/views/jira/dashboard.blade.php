@extends('layout.base_layout')

@section('content')

@include('layout.sidebar_nav')

<div class="col">
    <div class="container my-4">
        <!-- Заголовок -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-center fw-bold" style="color: #2c3e50;">Jira Dashboard</h1>
            <span class="badge bg-primary fs-5">Total Tasks: {{ count($issues) }}</span>
        </div>

        <!-- Фильтрация и поиск -->
        <div class="d-flex mb-4">
            <!-- Dropdown для версий -->
            <div class="me-2">
                <select class="form-select" id="versionFilter" style="height: 50px; font-size: 1.2rem;">
                    <option value="">---</option>
                    @php
                        $versions = [];

                        // Собираем уникальные версии
                        foreach ($issues as $issue) {
                            if (!empty($issue['fields']['fixVersions'])) {
                                foreach ($issue['fields']['fixVersions'] as $version) {
                                    $versions[] = $version['name'];
                                }
                            }
                        }

                        // Удаляем дубликаты версий
                        $versions = array_unique($versions);
                    @endphp

                    <!-- Добавляем версии в дропдаун -->
                    @foreach ($versions as $version)
                        <option value="{{ $version }}">{{ $version }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Поле поиска -->
            <div class="input-group">
                <span class="input-group-text bg-primary text-white">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control" id="search" placeholder="Поиск задач..." style="height: 50px; font-size: 1.2rem;">
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @php
            $statusOrder = [
                'WAIT:ESTIMATE' => 1,
                'PROCESS:DEFAULT' => 2,
                'PROCESS:TEST' => 2,
                'В работе' => 2,
                'WAIT:Test' => 3,
                'WAIT:DEFAULT' => 3,
            ];

            // Сортировка задач по статусу
            usort($issues, function ($a, $b) use ($statusOrder) {
                $statusA = $a['fields']['status']['name'];
                $statusB = $b['fields']['status']['name'];

                return ($statusOrder[$statusA] ?? PHP_INT_MAX) - ($statusOrder[$statusB] ?? PHP_INT_MAX);
            });
        @endphp

        @if(!empty($issues))
            <ul class="list-group">
                @foreach($issues as $issue)
                    <!-- Стилизуем задачи в виде карточек с тенями -->
                    <li class="list-group-item justify-content-between align-items-center shadow-sm mb-3 p-3 rounded border border-light">
                        <div>
                            <h5 class="mb-1 fw-bold">
                                <a href="http://jira.ab.loc/browse/{{ $issue['key'] }}" target="_blank" style="color: #007bff; text-decoration: none;">{{ $issue['key'] }}</a>
                            </h5>
                            <p class="mb-1 text-muted">{{ $issue['fields']['summary'] }}</p>
                            <!-- Размещаем версию, автора и статус в один ряд с помощью Flexbox -->
                            <div class="d-flex flex-wrap align-items-center">
                                <!-- Вывод версии -->
                                <small class="me-3">
                                    <strong>Version:</strong>
                                    @if(!empty($issue['fields']['fixVersions']))
                                        <span class="issue-version badge bg-light text-dark">{{ $issue['fields']['fixVersions'][0]['name'] }}</span>
                                    @else
                                        <span class="issue-version badge bg-secondary">No version specified</span>
                                    @endif
                                </small>

                                <!-- Вывод автора задачи -->
                                <small class="me-3">
                                    <strong>Author:</strong>
                                    @if(!empty($issue['fields']['reporter']))
                                        <span class="badge bg-info text-dark">{{ $issue['fields']['reporter']['displayName'] }}</span>
                                    @else
                                        <span class="badge bg-secondary">No author specified</span>
                                    @endif
                                </small>

                                <!-- Вывод статуса -->
                                <small>
                                    <strong>Status:</strong>
                                    <span class="badge
                                        @if($issue['fields']['status']['name'] === 'WAIT:ESTIMATE')
                                            border border-danger text-danger bg-light
                                        @elseif(in_array($issue['fields']['status']['name'], ['WAIT:Test', 'WAIT:DEFAULT']))
                                            border border-primary text-primary bg-light
                                        @elseif(in_array($issue['fields']['status']['name'], ['PROCESS:DEFAULT', 'В работе', 'PROCESS:TEST']))
                                            border border-success text-success bg-light
                                        @else
                                            bg-secondary
                                        @endif">
                                        {{ $issue['fields']['status']['name'] }}
                                    </span>
                                </small>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
<script>
    $(document).ready(function() {
        // Обработка фильтрации по версии
        $('#versionFilter').on('change', function() {
            let selectedVersion = $(this).val().toLowerCase().trim(); // Получаем выбранную версию
            let searchTerm = $('#search').val().toLowerCase().trim(); // Получаем текущий текст поиска

            $(".list-group-item").each(function() {
                let issueVersion = $(this).find('.issue-version').text().toLowerCase();
                let issueSummary = $(this).find('p.mb-1').text().toLowerCase(); // Саммари
                let issueKey = $(this).find('h5 a').text().toLowerCase(); // Номер задачи (issue key)
                let issueAuthor = $(this).find('.me-3').text().toLowerCase(); // Автор задачи

                let matchesVersion = (selectedVersion === '' || issueVersion.includes(selectedVersion));
                let matchesSearch = (issueSummary.includes(searchTerm) || issueKey.includes(searchTerm) || issueAuthor.includes(searchTerm));

                if (matchesVersion && matchesSearch) {
                    $(this).show(); // Показываем элемент <li>
                } else {
                    $(this).hide(); // Скрываем элемент <li>
                }
            });
        });

        // Обработка поиска по задачам
        $('#search').on('input', function() {
            let searchTerm = $(this).val().toLowerCase().trim(); // Получаем текст из поля поиска
            let selectedVersion = $('#versionFilter').val().toLowerCase().trim(); // Получаем текущую выбранную версию

            $(".list-group-item").each(function() {
                let issueVersion = $(this).find('.issue-version').text().toLowerCase();
                let issueSummary = $(this).find('p.mb-1').text().toLowerCase(); // Саммари
                let issueKey = $(this).find('h5 a').text().toLowerCase(); // Номер задачи (issue key)
                let issueAuthor = $(this).find('.me-3').text().toLowerCase(); // Автор задачи

                let matchesVersion = (selectedVersion === '' || issueVersion.includes(selectedVersion));
                let matchesSearch = (issueSummary.includes(searchTerm) || issueKey.includes(searchTerm) || issueAuthor.includes(searchTerm));

                if (matchesVersion && matchesSearch) {
                    $(this).show(); // Показываем элемент <li>
                } else {
                    $(this).hide(); // Скрываем элемент <li>
                }
            });
        });
    });
</script>
@endsection
