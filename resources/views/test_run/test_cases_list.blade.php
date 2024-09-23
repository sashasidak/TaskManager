<div class="tree_suite">

    @foreach($suites as $testSuite)

        {{-- SHOW CHILD SUITE TITLE WITH FULL PATH --}}
        <div class="suite_header" style="background: #7c879138; padding-left: 5px; padding-bottom: 5px; border: 1px solid lightgray; border-radius: 3px; position: relative;">
            <i class="bi bi-folder2 fs-5"></i>

            <span class="text-muted" style="font-size: 14px">
                @foreach($testSuite->ancestors()->get()->reverse() as $parent)
                    {{$parent->title}}
                    <i class="bi bi-arrow-right-short"></i>
                @endforeach
            </span>
            <span class="suite_title">{{$testSuite->title}}</span>

            {{-- Collapse/Expand Button --}}
            <button class="toggle-button" onclick="toggleTestCases(this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;">
                <i class="bi bi-chevron-down"></i>
            </button>
        </div>

        <div class="tree_suite_test_cases">
            @foreach($testSuite->testCases->sortBy('order') as $testCase)
                @if(in_array($testCase->id, $testCasesIds))

                    <div class="tree_test_case tree_test_case_content py-1 ps-1" onclick="loadTestCase({{$testRun->id}}, {{$testCase->id}})">
                        <div class='d-flex justify-content-between'>
                            <div class="mt-1">
                                <span>@if($testCase->automated) <i class="bi bi-robot"></i> @else <i class="bi bi-person"></i> @endif </span>
                                <span class="text-muted ps-1 pe-3 ">{{$repository->prefix}}-{{$testCase->id}}</span>
                                <span class="test_case_title">{{$testCase->title}}</span>
                            </div>
                            <div class="result_badge pe-2" data-test_case_id="{{$testCase->id}}">
                                @if(isset($results[$testCase->id]))
                                    @if($results[$testCase->id] == \App\Enums\TestRunCaseStatus::NOT_TESTED)
                                        <span class="badge bg-secondary">Not Tested</span>
                                    @elseif($results[$testCase->id] == \App\Enums\TestRunCaseStatus::PASSED)
                                        <span class="badge bg-success">Passed</span>
                                    @elseif($results[$testCase->id] == \App\Enums\TestRunCaseStatus::FAILED)
                                        <span class="badge bg-danger">Failed</span>
                                    @elseif($results[$testCase->id] == \App\Enums\TestRunCaseStatus::BLOCKED)
                                        <span class="badge bg-warning">Blocked</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Not Tested</span>
                                @endif
                            </div>
                        </div>
                    </div>

                @endif
            @endforeach
        </div>

    @endforeach

</div>

<script>
function toggleTestCases(button) {
    var testCases = button.closest('.suite_header').nextElementSibling;
    if (testCases.style.display === "none") {
        testCases.style.display = "block";
        button.innerHTML = '<i class="bi bi-chevron-down"></i>';
    } else {
        testCases.style.display = "none";
        button.innerHTML = '<i class="bi bi-chevron-right"></i>';
    }
}
</script>

<style>
.toggle-button i {
    font-size: 16px;
    color: darkgray;
}
</style>
