<?php

namespace App\Http\Controllers;

use App\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class JiraController extends Controller
{
    private $jiraDomain = 'jira.ab.loc'; // Ваш домен Jira

    public function getUserFromJira($email, $password)
    {
        $credentials = base64_encode($email . ':' . $password);
        $headers = ['Authorization' => 'Basic ' . $credentials];
        $url = "http://{$this->jiraDomain}/rest/api/2/myself";

        // Отправляем запрос к Jira
        $response = Http::withHeaders($headers)
            ->accept('application/json')
            ->get($url);

        if ($response->successful()) {
            return [
                'status' => 200,
                'data' => $response->json()
            ];
        }

        // Возвращаем ошибку, если запрос не прошел
        return [
            'status' => $response->status(),
            'error' => 'Error fetching user data from Jira'
        ];
    }

    public function dashboard(Request $request, $project_id = null)
    {
        $email = auth()->user()->name; // предположительно, что имя пользователя является email от Jira
        $password = auth()->user()->password; // предположительно, что пароль хранится без хеширования

        $project = null;

        if ($project_id) {
            $project = Project::find($project_id);
        }

        if (empty($email) || empty($password)) {
            Log::error('Jira Dashboard: Missing Jira credentials.');
            return back()->withErrors(['error' => 'Jira credentials are missing.']);
        }

        try {
            $credentials = base64_encode($email . ':' . $password);
            $headers = ['Authorization' => 'Basic ' . $credentials];
            $url = "http://{$this->jiraDomain}/rest/api/2/search?jql=assignee=currentUser() AND resolution = EMPTY&maxResults=1000";

            $response = Http::withHeaders($headers)
                ->accept('application/json')
                ->get($url);

            if ($response->successful()) {
                $issues = $response->json()['issues'];
                Log::info('Jira Dashboard: Issues retrieved successfully.', ['issues_count' => count($issues)]);
                return view('jira.dashboard', compact('issues', 'project'));
            } else {
                Log::error('Jira Dashboard: Failed to fetch issues.', ['response_body' => $response->body()]);
                return back()->withErrors(['error' => 'Error fetching issues from Jira.']);
            }
        } catch (\Exception $e) {
            Log::error('Jira Dashboard: Exception occurred.', ['exception' => $e->getMessage()]);
            return back()->withErrors(['error' => 'An unexpected error occurred while fetching data from Jira.']);
        }
    }

private function formatEstimate($seconds)
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);

    return "{$hours}h {$minutes}m";
}

public function getIssueEstimate($issueKey)
{
    $email = auth()->user()->name;
    $password = auth()->user()->password;

    if (empty($email) || empty($password)) {
        Log::error('JiraController: Missing Jira credentials.');
        return ['status' => 401, 'error' => 'Jira credentials are missing.'];
    }

    $credentials = base64_encode($email . ':' . $password);
    $headers = ['Authorization' => 'Basic ' . $credentials];
    $url = "http://{$this->jiraDomain}/rest/api/2/issue/{$issueKey}";


    try {
        $response = Http::withHeaders($headers)
            ->accept('application/json')
            ->get($url);


        if ($response->successful()) {
            $issueData = $response->json();
            $timeSpentInSeconds = $issueData['fields']['timespent'] ?? 0;
            $originalEstimateInSeconds = $issueData['fields']['timeoriginalestimate'] ?? 0;

            $timeSpent = $this->formatEstimate($timeSpentInSeconds);
            $originalEstimate = $this->formatEstimate($originalEstimateInSeconds);

            return [
                'status' => 200,
                'time_spent' => $timeSpent,
                'original_estimate' => $originalEstimate
            ];
        }

        return ['status' => $response->status(), 'error' => 'Error fetching issue data from Jira'];
    } catch (\Exception $e) {
        Log::error('JiraController: Exception occurred while fetching issue data', ['exception' => $e->getMessage()]);
        return ['status' => 500, 'error' => 'An unexpected error occurred while fetching data from Jira.'];
    }
}

public function getIssueId($issueKey)
{
    $email = auth()->user()->name;
    $password = auth()->user()->password;

    if (empty($email) || empty($password)) {
        Log::error('JiraController: Missing Jira credentials.');
        return null;
    }

    $credentials = base64_encode($email . ':' . $password);
    $headers = ['Authorization' => 'Basic ' . $credentials];
    $url = "http://{$this->jiraDomain}/rest/api/2/issue/{$issueKey}"; // Используем HTTP

    try {
        $response = Http::withHeaders($headers)
            ->accept('application/json')
            ->get($url);

        if ($response->successful()) {
            $issueData = $response->json();
            return $issueData['id']; // Получаем ID задачи
        } else {
            Log::error('JiraController: Failed to get issue data.', ['response_body' => $response->body()]);
            return null;
        }
    } catch (\Exception $e) {
        Log::error('JiraController: Exception occurred while getting issue data', ['exception' => $e->getMessage()]);
        return null;
    }
}

public function createBugReport(Request $request)
{
    $email = auth()->user()->name;
    $password = auth()->user()->password;

    if (empty($email) || empty($password)) {
        return response()->json(['error' => 'Jira credentials are missing.'], 400);
    }

    $credentials = base64_encode($email . ':' . $password);
    $headers = ['Authorization' => 'Basic ' . $credentials];
    $issueKey = $request->issue_key; // Ключ существующей задачи для получения информации о проекте

    // Получаем данные о существующей задаче
    $issueUrl = "http://{$this->jiraDomain}/rest/api/2/issue/{$issueKey}";

    try {
        // Получаем информацию о существующей задаче
        $issueResponse = Http::withHeaders($headers)
            ->accept('application/json')
            ->get($issueUrl);

        if (!$issueResponse->successful()) {
            return response()->json(['error' => 'Error fetching existing issue data from Jira.'], 500);
        }

        $issueData = $issueResponse->json();
        $projectKey = $issueData['fields']['project']['key'] ?? null;
        $fixVersions = $issueData['fields']['fixVersions'] ?? []; // Получаем значение "Исправить в версиях"
        $epicLink = $issueData['fields']['customfield_10000'] ?? null; // Получаем значение "Epic Link"

        if (!$projectKey) {
            return response()->json(['error' => 'Unable to retrieve project key from existing issue.'], 400);
        }

        // Получаем значение platform из запроса
        $platform = strtoupper($request->platform);

        // Устанавливаем компонент в зависимости от платформы
        $componentId = null;
        switch ($platform) {
            case 'IOS':
                $componentId = '10499';
                break;
            case 'AOS':
                $componentId = '10500';
                break;
            case 'BACK':
                $componentId = '12812';
                break;
        }

        // Проверяем, был ли найден компонент
        if ($componentId === null) {
            return response()->json(['error' => 'Invalid platform specified.'], 400);
        }

        $customerKey = strtoupper($request->customer_key);
        $executorKey = strtoupper($request->executor_key);
        $severity = $request->severity;
        $errorType = $request->error_type;
        $issueTypeId = '';

        if ($errorType === 'bug_fix') {
            $issueTypeId = '10803';
        } elseif ($errorType === 'design_issue') {
            $issueTypeId = '11700';
        } elseif ($errorType === 'bug') {
            $issueTypeId = '10102'; // Проверка для "bug"
        }

        // Определение серьезности (severity) для вставки в summary
        $severityMap = [
            '10200' => 'S1',
            '10201' => 'S2',
            '10202' => 'S3',
            '10203' => 'S4',
            '10204' => 'S5',
            '-1'    => 'Not Selected'
        ];

        // Получаем значение S# в зависимости от severity
        $severityLabel = $severityMap[$severity] ?? 'Unknown';

        // Формируем summary
        $summary = "[{$platform}][{$severityLabel}] {$request->subject}";

        // Преобразование HTML в Markdown
        function htmlToMarkdown($html) {
            // Удаляем HTML-теги, кроме необходимых
            $html = strip_tags($html, '<p><b><i><ul><ol><li><code>');

            // Замена <p> на пустую строку
            $html = preg_replace('/<p>/', "\n\n", $html);
            $html = preg_replace('/<\/p>/', "\n\n", $html);

            // Замена <b> на **
            $html = preg_replace('/<b>/', '*', $html);
            $html = preg_replace('/<\/b>/', '*', $html);

            // Замена <i> на *
            $html = preg_replace('/<i>/', '*', $html);
            $html = preg_replace('/<\/i>/', '*', $html);

            // Замена <ul> и <ol> на пустую строку
            $html = preg_replace('/<ul>/', "\n", $html);
            $html = preg_replace('/<\/ul>/', "\n", $html);
            $html = preg_replace('/<ol>/', "\n", $html);
            $html = preg_replace('/<\/ol>/', "\n", $html);

            // Замена <li> на - для <ul> и 1. для <ol>
            $html = preg_replace('/<li>/', '- ', $html);
            $html = preg_replace('/<\/li>/', "\n", $html);
            $html = preg_replace('/<ol>/', "\n", $html);
            $html = preg_replace('/<\/ol>/', "\n", $html);

            // Замена <ol><li> на нумерованный список
            $html = preg_replace('/<ol>\s*<li>/', '1. ', $html);
            $html = preg_replace('/<\/ol>\s*<\/li>/', "\n", $html);

            // Замена <code> на ``
            $html = preg_replace('/<code>/', '`', $html);
            $html = preg_replace('/<\/code>/', '`', $html);

            return trim($html);
        }


        $stepsMarkdown = htmlToMarkdown($request->steps);
        $actualResultMarkdown = htmlToMarkdown($request->actual_result);
        $expectedResultMarkdown = htmlToMarkdown($request->expected_result);

        $descriptionMarkdown = "*Шаги:*\n" . $stepsMarkdown . "\n\n*Фактический результат:*\n" . $actualResultMarkdown . "\n\n*Ожидаемый результат:*\n" . $expectedResultMarkdown;

        $createUrl = "http://{$this->jiraDomain}/rest/api/2/issue";
        $createData = [
            'fields' => [
                'project' => [
                    'key' => $projectKey
                ],
                'summary' => $summary, // Используем сформированный summary
                'description' => $descriptionMarkdown, // Используем преобразованный Markdown
                'issuetype' => [
                    'id' => $issueTypeId
                ],
                // Устанавливаем заказчика и исполнителя
                'customfield_10210' => [
                    'name' => $customerKey // Передаем ключ заказчика в верхнем регистре
                ],
                'assignee' => [
                    'name' => $executorKey // Передаем ключ исполнителя в верхнем регистре
                ],
                // Передаем значения "Исправить в версиях"
                'fixVersions' => array_map(function($version) {
                    return ['id' => $version['id']];
                }, $fixVersions),
                // Передаем "Epic Link"
                'customfield_10000' => $epicLink,
                // Передаем серьезность
                'customfield_10300' => [
                    'id' => $severity // Передаем ID серьезности
                ],
                // Передаем customfield_11023 или environment в зависимости от issueTypeId
                $issueTypeId === '10102' ? 'environment' : 'customfield_11023' => $issueTypeId === '10102' ? $request->device : $request->device
            ]
        ];

        // Устанавливаем компонент для всех типов задач
        $createData['fields']['components'] = [
            ['id' => $componentId]
        ];

        // Если issueTypeId == '10102', добавляем дополнительные поля
        if ($issueTypeId === '10102') {
            $today = Carbon::today()->format('Y-m-d');  // Пример: 2024-09-19
            $nextWeek = Carbon::today()->addDays(7)->format('Y-m-d');  // Пример: 2024-09-26

            // Не передаем customfield_10000
            unset($createData['fields']['customfield_10000']);

            $createData['fields']['customfield_10201'] = $today;
            $createData['fields']['customfield_10202'] = $nextWeek;

            $createData['fields']['timetracking'] = [
                'originalEstimate' => '1w',
                'remainingEstimate' => '1w'
            ];

            $createData['fields']['labels'] = ['IT'];
        }

        Log::info('JiraController: Sending create issue request', ['url' => $createUrl, 'headers' => $headers, 'data' => $createData]);

        $createResponse = Http::withHeaders($headers)
            ->accept('application/json')
            ->post($createUrl, $createData);

        Log::info('JiraController: Received response from Jira', ['status' => $createResponse->status(), 'response_body' => $createResponse->body()]);

        if ($createResponse->status() == 201) {
            $newIssueKey = $createResponse->json()['key'];

            $this->linkIssues($issueKey, $newIssueKey, $headers, $errorType);
            $this->handleAttachments($request->file('attachments'), $newIssueKey, $headers);

            return response()->json(['success' => 'Задача успешно создана в Jira и связана с существующей задачей!']);
        } else {
            return response()->json(['error' => 'Error creating issue in Jira.'], 500);
        }
    } catch (\Exception $e) {
        Log::error('Exception while creating issue in Jira:', ['exception' => $e->getMessage()]);
        return response()->json(['error' => 'An unexpected error occurred while creating issue in Jira.'], 500);
    }
}

private function handleAttachments($files, $issueKey, $headers)
{
    if (!$files) {
        return;
    }

    $url = "http://{$this->jiraDomain}/rest/api/2/issue/{$issueKey}/attachments";

    foreach ($files as $file) {
        try {
            $response = Http::withHeaders(array_merge($headers, ['X-Atlassian-Token' => 'no-check']))
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post($url);

            Log::info('JiraController: Received response from Jira (attachment)', ['status' => $response->status(), 'response_body' => $response->body()]);

            if (!$response->successful()) {
                Log::error('JiraController: Failed to attach file.', ['response_body' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error('JiraController: Exception occurred while attaching file', ['exception' => $e->getMessage()]);
        }
    }
}



private function linkIssues($sourceIssueKey, $targetIssueKey, $headers, $errorType)
{
    $url = "http://{$this->jiraDomain}/rest/api/2/issueLink";

    // Определяем тип связи в зависимости от типа ошибки
    $linkTypeId = ($errorType === 'bug') ? '10305' : '10401';

    $data = [
        'type' => [
            'id' => $linkTypeId
        ],
        'inwardIssue' => [
            'key' => $sourceIssueKey
        ],
        'outwardIssue' => [
            'key' => $targetIssueKey
        ]
    ];

    try {
        $response = Http::withHeaders($headers)
            ->accept('application/json')
            ->post($url, $data);

        if ($response->status() == 201) {
        } else {
            Log::error('JiraController: Failed to link issues.', ['response_body' => $response->body()]);
        }
    } catch (\Exception $e) {
        Log::error('JiraController: Exception occurred while linking issues', ['exception' => $e->getMessage()]);
    }
}

public function searchCustomer(Request $request)
{
    $query = $request->input('query');
    $email = auth()->user()->name;
    $password = auth()->user()->password;

    if (empty($email) || empty($password)) {
        Log::error('JiraController: Missing Jira credentials for searchCustomer.');
        return response()->json(['error' => 'Jira credentials are missing.'], 401);
    }

    $credentials = base64_encode($email . ':' . $password);
    $headers = ['Authorization' => 'Basic ' . $credentials];

    // URL для поиска пользователя по имени
    $url = "http://{$this->jiraDomain}/rest/api/2/user/search?username=" . urlencode($query);

    try {
        $response = Http::withHeaders($headers)
            ->accept('application/json')
            ->get($url);

        // Логирование тела ответа
        Log::info('JiraController: Response from Jira (searchCustomer)', [
            'status' => $response->status(),
            'response_body' => $response->body()
        ]);

        if (!$response->successful()) {
            Log::error('JiraController: Failed to search for customers.', ['status' => $response->status(), 'response_body' => $response->body()]);
            return response()->json(['error' => 'Error searching for customers.'], $response->status());
        }

        $users = $response->json();

        return response()->json($users, 200);

    } catch (\Exception $e) {
        Log::error('JiraController: Exception occurred during customer search', ['exception' => $e->getMessage()]);
        return response()->json(['error' => 'An unexpected error occurred while searching for customers.'], 500);
    }
}

public function searchExecutor(Request $request)
{
    $query = $request->input('query');
    $email = auth()->user()->name;
    $password = auth()->user()->password;

    if (empty($email) || empty($password)) {
        Log::error('JiraController: Missing Jira credentials for searchExecutor.');
        return response()->json(['error' => 'Jira credentials are missing.'], 401);
    }

    $credentials = base64_encode($email . ':' . $password);
    $headers = ['Authorization' => 'Basic ' . $credentials];

    // URL для поиска исполнителя по имени
    $url = "http://{$this->jiraDomain}/rest/api/2/user/search?username=" . urlencode($query);

    try {
        $response = Http::withHeaders($headers)
            ->accept('application/json')
            ->get($url);

        Log::info('JiraController: Response from Jira (searchExecutor)', [
            'status' => $response->status(),
            'response_body' => $response->body()
        ]);

        if (!$response->successful()) {
            Log::error('JiraController: Failed to search for executors.', ['status' => $response->status(), 'response_body' => $response->body()]);
            return response()->json(['error' => 'Error searching for executors.'], $response->status());
        }

        $users = $response->json();

        return response()->json($users, 200);

    } catch (\Exception $e) {
        Log::error('JiraController: Exception occurred during executor search', ['exception' => $e->getMessage()]);
        return response()->json(['error' => 'An unexpected error occurred while searching for executors.'], 500);
    }
}

}
