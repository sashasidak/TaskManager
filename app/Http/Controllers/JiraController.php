<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
}
