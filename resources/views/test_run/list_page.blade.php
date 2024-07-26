@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    <div class="col">

        <div class="border-bottom my-3">
            <h3 class="page_title">
                Test Runs

                @can('add_edit_test_runs')
                    <a class="mx-3" href="{{route("test_run_create_page", $project->id)}}">
                        <button type="button" class="btn btn-sm btn-primary"> <i class="bi bi-plus-lg"></i> New Test Run</button>
                    </a>
                @endcan

            </h3>
        </div>

        <div class="row row-cols-1 row-cols-md-3 g-3">
            @foreach($testRuns as $testRun)

                <div class="col">
                    <div class="base_block shadow h-100 rounded border">

                        <div class="card-body d-flex justify-content-between ">
                            <div>
                                <a class="fs-4" href="{{route('test_run_show_page', [$project->id, $testRun->id])}}">
                                    <i class="bi bi-play-circle"></i> {{$testRun->title}}
                                </a>
                            </div>

                            <div>
                                <span class="text-muted" title="created at">{{$testRun->created_at->format('d-m-Y')}} </span>
                            </div>
                        </div>

                        @if($testRun->testPlan && $testRun->testPlan->description)
                            <div class="card-text text-muted ps-3">
                                <span>{!! preg_replace(
                                   '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#',
                                   '<a href="$0" target="_blank">$0</a>',
                                   e($testRun->testPlan->description)
                               ) !!}</span>
                            </div>
                        @endif

                        <div class="border-top p-2">
                            @include('test_run.chart')
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

    </div>
@endsection
