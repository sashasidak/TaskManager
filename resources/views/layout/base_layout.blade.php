<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>àbank24TMS - Open Source Test Management Tool</title>

    <link rel="icon" type="image/x-icon" href="{{asset('/img/favicon.ico')}}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.2/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
    <script src="{{asset('js/js.cookie.min.js')}}"></script>

    <link href="{{asset('css/main.css')}}" rel="stylesheet">

    @yield('head')
</head>
<body>

<div class="row sticky-top">
    @include('layout.header_nav')
</div>

<div class="container-fluid">

    <div class="row fh">

           @yield('content')

    </div>

</div>

<div class="modal fade" id="any_img_lightbox" tabindex="-1" aria-labelledby="exampleModalCenterTitle" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="position-absolute top-50 start-50 translate-middle">
            <img id="any_img_lightbox_image" src="" alt="">
        </div>
    </div>
</div>

<script src="{{asset('js/main.js')}}"></script>


@yield('footer')
</body>
</html>
