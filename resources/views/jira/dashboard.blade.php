@extends('layout.base_layout')

@section('content')

@include('layout.sidebar_nav')

<div class="col">
    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h1 class="text-center">Jira Dashboard</h1>
            <span class="badge bg-primary fs-5">Total Tasks: {{ count($issues) }}</span>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @php
            $groupedIssues = [
                'WAIT:ESTIMATE' => [],
                'versions' => [],
                'noVersion' => [] // Группа для задач без версии
            ];

            // Разделение задач на группы
            foreach ($issues as $issue) {
                $status = $issue['fields']['status']['name'];
                $version = !empty($issue['fields']['fixVersions']) ? $issue['fields']['fixVersions'][0]['name'] : null;

                if ($status === 'WAIT:ESTIMATE') {
                    $groupedIssues['WAIT:ESTIMATE'][] = $issue;
                } elseif ($version) {
                    $groupedIssues['versions'][$version][] = $issue;
                } else {
                    $groupedIssues['noVersion'][] = $issue; // Добавляем задачи без версии в отдельную группу
                }
            }

            // Определяем порядок статусов
            $statusOrder = [
                'PROCESS:DEFAULT' => 1,
                'В работе' => 2,
                'WAIT:DEFAULT' => 3,
                'WAIT:Test' => 4,
            ];

            // Сортировка задач в WAIT:ESTIMATE по статусу
            usort($groupedIssues['WAIT:ESTIMATE'], function ($a, $b) use ($statusOrder) {
                return ($statusOrder[$a['fields']['status']['name']] ?? PHP_INT_MAX) -
                       ($statusOrder[$b['fields']['status']['name']] ?? PHP_INT_MAX);
            });

            // Сортировка задач внутри каждой версии по статусу
            foreach ($groupedIssues['versions'] as &$issuesByVersion) {
                usort($issuesByVersion, function ($a, $b) use ($statusOrder) {
                    return ($statusOrder[$a['fields']['status']['name']] ?? PHP_INT_MAX) -
                           ($statusOrder[$b['fields']['status']['name']] ?? PHP_INT_MAX);
                });
            }

            // Сортировка задач без версии по статусу
            usort($groupedIssues['noVersion'], function ($a, $b) use ($statusOrder) {
                return ($statusOrder[$a['fields']['status']['name']] ?? PHP_INT_MAX) -
                       ($statusOrder[$b['fields']['status']['name']] ?? PHP_INT_MAX);
            });

            // Сортируем версии по названию, если требуется
            uksort($groupedIssues['versions'], function ($a, $b) {
                if (preg_match('/\d/', $a) && !preg_match('/\d/', $b)) return -1;
                if (!preg_match('/\d/', $a) && preg_match('/\d/', $b)) return 1;
                if (preg_match('/\d/', $a) && preg_match('/\d/', $b)) {
                    preg_match('/\d+(\.\d+)*/', $a, $matchesA);
                    preg_match('/\d+(\.\d+)*/', $b, $matchesB);
                    return version_compare($matchesA[0] ?? 'z', $matchesB[0] ?? 'z');
                }
                return 0;
            });
        @endphp

        @if(!empty($groupedIssues))
            <ul class="nav nav-tabs" id="issuesTabs" role="tablist">
                @if(!empty($groupedIssues['WAIT:ESTIMATE']))
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="tabWaitEstimate" data-bs-toggle="tab" href="#contentWaitEstimate" role="tab" aria-controls="contentWaitEstimate" aria-selected="true">WAIT:ESTIMATE</a>
                    </li>
                @endif

                @foreach($groupedIssues['versions'] as $version => $issuesByVersion)
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="tabVersion{{ $loop->index }}" data-bs-toggle="tab" href="#contentVersion{{ $loop->index }}" role="tab" aria-controls="contentVersion{{ $loop->index }}" aria-selected="false">Version: {{ $version }}</a>
                    </li>
                @endforeach

                @if(!empty($groupedIssues['noVersion']))
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="tabNoVersion" data-bs-toggle="tab" href="#contentNoVersion" role="tab" aria-controls="contentNoVersion" aria-selected="false">Version: No Version</a>
                    </li>
                @endif
            </ul>

            <div class="tab-content mt-3" id="issuesTabsContent">
                @if(!empty($groupedIssues['WAIT:ESTIMATE']))
                    <div class="tab-pane fade show active" id="contentWaitEstimate" role="tabpanel" aria-labelledby="tabWaitEstimate">
                        <div class="row">
                            @foreach($groupedIssues['WAIT:ESTIMATE'] as $issue)
                                <div class="col-md-6 mb-3">
                                    <div class="card" style="border: 1px solid #ddd; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); background-color: #ffffff; border-radius: 8px;">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <a href="http://jira.ab.loc/browse/{{ $issue['key'] }}" target="_blank" style="color: #007bff; text-decoration: none;">{{ $issue['key'] }}</a>
                                            </h5>
                                            <p class="card-text">{{ $issue['fields']['summary'] }}</p>
                                            <hr>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <p class="mb-0">
                                                    <strong>Version:</strong>
                                                    @if(!empty($issue['fields']['fixVersions']))
                                                        <span class="badge bg-light text-dark">{{ $issue['fields']['fixVersions'][0]['name'] }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">No version specified</span>
                                                    @endif
                                                </p>
                                                <p class="mb-0">
                                                    <strong>Status:</strong>
                                                    <span class="badge
                                                        @if($issue['fields']['status']['name'] === 'WAIT:ESTIMATE')
                                                            border border-danger text-danger
                                                        @elseif(in_array($issue['fields']['status']['name'], ['WAIT:Test', 'WAIT:DEFAULT']))
                                                            border border-primary text-primary
                                                        @elseif(in_array($issue['fields']['status']['name'], ['PROCESS:DEFAULT', 'В работе']))
                                                            border border-success text-success
                                                        @else
                                                            bg-secondary
                                                        @endif">
                                                        {{ $issue['fields']['status']['name'] }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @foreach($groupedIssues['versions'] as $version => $issuesByVersion)
                    <div class="tab-pane fade" id="contentVersion{{ $loop->index }}" role="tabpanel" aria-labelledby="tabVersion{{ $loop->index }}">
                        <div class="row">
                            @foreach($issuesByVersion as $issue)
                                <div class="col-md-6 mb-3">
                                    <div class="card" style="border: 1px solid #ddd; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); background-color: #ffffff; border-radius: 8px;">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <a href="http://jira.ab.loc/browse/{{ $issue['key'] }}" target="_blank" style="color: #007bff; text-decoration: none;">{{ $issue['key'] }}</a>
                                            </h5>
                                            <p class="card-text">{{ $issue['fields']['summary'] }}</p>
                                            <hr>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <p class="mb-0">
                                                    <strong>Version:</strong>
                                                    <span class="badge bg-light text-dark">{{ $version }}</span>
                                                </p>
                                                <p class="mb-0">
                                                    <strong>Status:</strong>
                                                    <span class="badge
                                                        @if($issue['fields']['status']['name'] === 'WAIT:ESTIMATE')
                                                            border border-danger text-danger
                                                        @elseif(in_array($issue['fields']['status']['name'], ['WAIT:Test', 'WAIT:DEFAULT']))
                                                            border border-primary text-primary
                                                        @elseif(in_array($issue['fields']['status']['name'], ['PROCESS:DEFAULT', 'В работе']))
                                                            border border-success text-success
                                                        @else
                                                            bg-secondary
                                                        @endif">
                                                        {{ $issue['fields']['status']['name'] }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                @if(!empty($groupedIssues['noVersion']))
                    <div class="tab-pane fade" id="contentNoVersion" role="tabpanel" aria-labelledby="tabNoVersion">
                        <div class="row">
                            @foreach($groupedIssues['noVersion'] as $issue)
                                <div class="col-md-6 mb-3">
                                    <div class="card" style="border: 1px solid #ddd; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); background-color: #ffffff; border-radius: 8px;">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <a href="http://jira.ab.loc/browse/{{ $issue['key'] }}" target="_blank" style="color: #007bff; text-decoration: none;">{{ $issue['key'] }}</a>
                                            </h5>
                                            <p class="card-text">{{ $issue['fields']['summary'] }}</p>
                                            <hr>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <p class="mb-0">
                                                    <strong>Version:</strong>
                                                    <span class="badge bg-secondary">No version specified</span>
                                                </p>
                                                <p class="mb-0">
                                                    <strong>Status:</strong>
                                                    <span class="badge
                                                        @if($issue['fields']['status']['name'] === 'WAIT:ESTIMATE')
                                                            border border-danger text-danger
                                                        @elseif(in_array($issue['fields']['status']['name'], ['WAIT:Test', 'WAIT:DEFAULT']))
                                                            border border-primary text-primary
                                                        @elseif(in_array($issue['fields']['status']['name'], ['PROCESS:DEFAULT', 'В работе']))
                                                            border border-success text-success
                                                        @else
                                                            bg-secondary
                                                        @endif">
                                                        {{ $issue['fields']['status']['name'] }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
