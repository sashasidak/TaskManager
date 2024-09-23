
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="This is a login page template based on Bootstrap 5">
    <title>QaraTMS - Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<section class="h-100">
    <div class="container h-100">
        <div class="row justify-content-sm-center h-100">
            <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-7 col-sm-9">

                <div class="text-center my-5">
                    <img src="{{asset('/img/Logo Abank@3x.png')}}" alt="logo" width="300">
                </div>

                <div class="card shadow">
                    <div class="card-body p-5">
                        <h1 class="fs-4 card-title fw-bold mb-4">Login</h1>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <div>
                                    @foreach ($errors->all() as $error)
                                        <span>{{ $error }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('auth') }}">
                            @csrf

                            <div class="form-group mb-3">
                                <input type="text" placeholder="Login" id="email" class="form-control" name="email" required
                                       autofocus>
                            </div>

                            <div class="form-group mb-3">
                                <input type="password" placeholder="Password" id="password" class="form-control" name="password" required>
                            </div>

                            <div class="d-flex align-items-center">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember"> Remember Me
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary ms-auto">
                                    Login
                                </button>
                            </div>
                        </form>
                    </div>


                </div>

                <div class="text-center mt-5 text-muted">
                   àbank24TMS
                </div>
            </div>
        </div>
    </div>
</section>

</body>
</html>





