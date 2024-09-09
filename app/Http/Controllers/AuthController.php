<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\JiraController;
use App\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected $jira;
    /*****************************************
     *  LOGIN
     *****************************************/

    public function __construct(JiraController $jira)
    {
        $this->jira = $jira;
    }

    // Показать страницу входа
    public function showLoginPage()
    {
        if (Auth::check()) {
            return redirect('/');
        }

        return view('auth.login_page');
    }

    // Авторизация пользователя
    public function authorizeUser(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $email = $credentials['email'];
        $password = $credentials['password'];

        // Проверяем, если email равен admin@admin.com
        if ($email === 'admin@admin.com') {
            // Логиним пользователя напрямую
            $user = User::where('email', $email)->first();

            if (!$user) {
                // Создаем пользователя, если его нет в базе
                $user = User::create([
                    'name' => 'Admin', // Можно изменить имя на что-то подходящее
                    'email' => $email,
                    'password' => Hash::make($password),
                ]);
            }

            // Аутентифицируем пользователя
            Auth::login($user);
            return redirect()->intended('/');
        }

        // Отправляем запрос к Jira
        $jiraResponse = $this->jira->getUserFromJira($email, $password);

        if ($jiraResponse['status'] == 200) {
            $jiraUser = $jiraResponse['data'];
            $email = $jiraUser['emailAddress'];

            // Ищем пользователя в базе по email
            $user = User::where('email', $email)->first();

            if (!$user) {
                // Создаем пользователя, если его нет в базе
                $user = User::create([
                    'name' => $request->input('email'), // Используем имя из формы
                    'email' => $email,
                    'password' => Hash::make($password),
                ]);
            }

            // Аутентифицируем пользователя
            Auth::login($user);
            return redirect()->intended('/');
        }

        // Если авторизация в Jira не прошла, возвращаем ошибку
        return redirect()->route('login_page')->withErrors('Login details are not valid or Jira authentication failed');
    }
    /*****************************************
     *  LOGOUT
     *****************************************/

    public function logout() {
        Session::flush();
        Auth::logout();
        return redirect()->route('login_page');
    }
}
