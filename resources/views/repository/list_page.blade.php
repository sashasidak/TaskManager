@extends('layout.base_layout')

@section('content')

@include('layout.sidebar_nav')

<div class="col">

    <div class="border-bottom my-3">
        <h3 class="page_title">
            Repositories

            @can('add_edit_repositories')
                <a class="mx-3" href="{{ route('repository_create_page', $project->id) }}">
                    <button type="button" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg"></i> Add New
                    </button>
                </a>

                <!-- Кнопка для открытия модального окна -->
                <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#copyTasksModal">
                    <i class="bi bi-arrow-right"></i> Copy Tasks
                </button>
            @endcan
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
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="copyTasksModalLabel">Copy Tasks</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="copy-tasks-form" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="source-repository" class="form-label">Source Repository</label>
                            <select id="source-repository" name="source_repository" class="form-select" required>
                                @foreach($repositories as $repository)
                                    <option value="{{ $repository->id }}">{{ $repository->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="target-repository" class="form-label">Target Repository</label>
                            <select id="target-repository" name="target_repository" class="form-select" required>
                                @foreach($repositories as $repository)
                                    <option value="{{ $repository->id }}">{{ $repository->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
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
<script>
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

    document.getElementById('copy-tasks-form').addEventListener('submit', function (e) {
        e.preventDefault(); // Предотвращает стандартное действие формы

        const sourceRepoId = document.getElementById('source-repository').value;
        const targetRepoId = document.getElementById('target-repository').value;

        // Установка URL для формы
        this.action = `{{ url('/copy-tasks') }}/${sourceRepoId}/${targetRepoId}`;

        // Отправка формы
        this.submit();
    });
</script>
@endsection
