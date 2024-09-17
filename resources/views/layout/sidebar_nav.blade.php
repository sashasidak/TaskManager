<div class="col-auto sidebar shadow-sm">
    <div style="margin-top: 20px;">

        <!-- Кнопка Jira Dashboard -->
        @if(isset($project))
            <a href="{{ route('jira_dashboard', ['project_id' => $project->id]) }}" class="nav-link text-white">
                <i class="bi bi-kanban"></i> Jira Dashboard
            </a>
        @else
            <a href="{{ route('jira_dashboard') }}" class="nav-link text-white">
                <i class="bi bi-kanban"></i> Jira Dashboard
            </a>
        @endif

        <hr>

        @if(isset($project))
            <a href="{{ route('project_show_page', $project->id) }}" class="nav-link text-white sidebar_project_title">
                <i class="bi bi-kanban-fill"></i>
                {{ $project->title }}
            </a>

            <hr>

            <a href="{{ route('repository_list_page', $project->id) }}" class="nav-link text-white">
                <i class="bi bi-server"></i>
                Repositories
            </a>

            <a href="{{ route('test_run_list_page', $project->id) }}" class="nav-link text-white">
                <i class="bi bi-play-circle"></i> Test Runs
            </a>

            <a href="{{ route('project_documents_list_page', $project->id) }}" class="nav-link text-white">
                <i class="bi bi-file-text-fill"></i> Documents
            </a>

            <hr>
        @endif

        <a href="{{ route('project_list_page') }}" class="nav-link text-white">
            <i class="bi bi-diagram-3-fill"></i>
            All Projects
        </a>

        <a href="{{ route('users_list_page') }}" class="nav-link text-white">
            <i class="bi bi-people-fill"></i>
            Users
        </a>

        <hr>

        <a href="{{ route('logout') }}" class="nav-link text-white">
            <i class="bi bi-box-arrow-in-left"></i>
            <b>Logout</b>
        </a>

        <hr>

        <!-- Отображение имени текущего пользователя -->
        <div class="text-white text-center mt-4">
            <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
        </div>

        <hr>

        <div class="text-center text-white mt-4">
            <small>Version 3.8.1</small>
        </div>

    </div>
</div>
