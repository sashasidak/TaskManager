<div class="tree_suite">

    <div class="tree_test_suite_content d-flex justify-content-start">
        <div class="form-check">
            <input class="form-check-input test_suite_cbx" type="checkbox" value=""
                   data-test_suite_id="{{$testSuite->id}}"
                   data-parent_id="{{$testSuite->parent_id}}">
        </div>

        <div>
            <i class="bi bi-folder2 fs-4"></i>
            <span><b class="suiteTitle" data-title="{{$testSuite->title}}">{{$testSuite->title}}</b></span>
        </div>
    </div>

    <div class="tree_suite_test_cases">
        @foreach($testSuite->testCases->sortBy('order') as $testCase)

            <div class="tree_test_case tree_test_case_content">
                <div class='tree_test_case_click d-flex justify-content-start'>
                    <div class="form-check">
                        <input class="form-check-input test_case_cbx" type="checkbox" value=""
                               data-test_suite_id="{{$testSuite->id}}"
                               data-test_case_id="{{$testCase->id}}">
                    </div>

                    <div>
                        <span>@if($testCase->automated) <i class="bi bi-robot"></i> @else <i class="bi bi-person"></i> @endif </span>
                        <span>@if($testCase->need_on_smoke) <i class="bi bi-cloud-fog2"></i> @else <i class="bi bi-clipboard2-check"></i> @endif </span>
                        <span class="text-muted ps-2 test_case_id">{{$prefix}}-{{$testCase->id}}</span>
                        <span>{{$testCase->title}}</span>
                    </div>
                </div>
            </div>

        @endforeach
    </div>

    @foreach($testSuite->children as $testSuite)
        @include('test_plan.tree_item')
    @endforeach

</div>

<script>
function shortenUrls(text) {
    const urlPattern = /(\b(https?|ftp|file):\/\/jira\.ab\.loc\/browse\/(\w+-\d+))/gi;
    return text.replace(urlPattern, (match, fullUrl, protocol, shortUrl) => {
        const shortenedText = shortUrl; // Например, A24MOB-33433
        return `<a href="${fullUrl}" class="branch-link" target="_blank">${shortenedText}</a>`;
    });
}

function updateSuiteTitles() {
    document.querySelectorAll('.suiteTitle').forEach(span => {
        const originalTitle = span.getAttribute('data-title');
        span.innerHTML = shortenUrls(originalTitle);
    });
}

// Вызывайте эту функцию, чтобы обновить ссылки после загрузки страницы
document.addEventListener('DOMContentLoaded', updateSuiteTitles);
</script>
