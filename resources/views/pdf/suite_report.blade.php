<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Test Run Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .test-case {
            border: 1px solid #ddd;
            margin-bottom: 10px;
            padding: 10px;
        }
    </style>
</head>
<body>

<h1>Test Run Report for Project: {{ $project->name }}</h1>
<h2>Test Run: {{ $testRun->title }}</h2>

@foreach($suites as $suite)
    <h3>{{ $suite->title }}</h3>
    @foreach($suite->testCases as $testCase)
        @if($testCases->contains($testCase))
            <div class="test-case">
                <p><strong>ID:</strong> {{ $testCase->id }}</p>
                <p><strong>Title:</strong> {{ $testCase->title }}</p>
                <p><strong>Status:</strong> {{ $results[$testCase->id] ?? 'Not Tested' }}</p>
            </div>
        @endif
    @endforeach
@endforeach

</body>
</html>
