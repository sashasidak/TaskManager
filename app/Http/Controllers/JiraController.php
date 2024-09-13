<?php

namespace App\Http\Controllers;

use App\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

    Log::info('JiraController: Sending request to Jira', ['url' => $url, 'headers' => $headers]);

    try {
        $response = Http::withHeaders($headers)
            ->accept('application/json')
            ->get($url);

        Log::info('JiraController: Received response from Jira', ['status' => $response->status(), 'response_body' => $response->body()]);

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

}
