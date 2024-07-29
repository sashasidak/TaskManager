@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    <div class="col">

        <div class="border-bottom my-3">
            <h3 class="page_title">
                Repositories

                @can('add_edit_repositories')
                    <a class="mx-3" href="{{route("repository_create_page", $project->id)}}">
                        <button type="button" class="btn btn-sm btn-primary"> <i class="bi bi-plus-lg"></i> Add New</button>
                    </a>
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
    </script>
@endsection
