<div class="col-auto sidebar shadow-sm">
    <div style="margin-top: 20px;">

        @if(isset($project))

            <a href="{{route("project_show_page", $project->id)}}" class="nav-link text-white sidebar_project_title">
                <i class="bi bi-kanban-fill"></i>
                {{$project->title}}
            </a>

            <hr>

            <a href="{{route("repository_list_page", $project->id)}}" class="nav-link text-white">
                <i class="bi bi-server"></i>
                Repositories
            </a>

            {{-- <a href="{{route("test_plan_list_page", $project->id)}}" class="nav-link text-white">
                <i class="bi bi-journals"></i> Test Plans
            </a>  скрыл с бокового бара когда переделал содание test_run, убрав шаги с созданием Test_plan--}}

            <a href="{{route("test_run_list_page", $project->id)}}" class="nav-link text-white">
                <i class="bi bi-play-circle"></i> Test Runs
            </a>

            <a href="{{route("project_documents_list_page", $project->id)}}" class="nav-link text-white">
                <i class="bi bi-file-text-fill"></i> Documents
            </a>

            <hr>
        @endif

        <a href="{{route("project_list_page")}}" class="nav-link text-white">
            <i class="bi bi-diagram-3-fill"></i>
            All Projects
        </a>

        <a href="{{route('users_list_page')}}" class="nav-link text-white">
            <i class="bi bi-people-fill"></i>
            Users
        </a>

        <hr>

        <a href="{{route('logout')}}" class="nav-link text-white">
            <i class="bi bi-box-arrow-in-left"></i>
            <b>Logout</b>
        </a>

        <hr>

        <div class="text-center text-white mt-4">
            <small>Version 2.2.3</small>
        </div>

    </div>
</div>
