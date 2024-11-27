<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Clearlink')</title>

    <link rel="icon" href="{{  asset('assets/images/favicon-32x32.png') }}" type="image/x-icon" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">

    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <link href="{{asset('assets/css/partner.css') }}" rel="stylesheet">

    <script type="text/javascript">
        (function(c, l, a, r, i, t, y) {
            c[a] = c[a] || function() {
                (c[a].q = c[a].q || []).push(arguments)
            };
            t = l.createElement(r);
            t.async = 1;
            t.src = "https://www.clarity.ms/tag/" + i;
            y = l.getElementsByTagName(r)[0];
            y.parentNode.insertBefore(t, y);
        })(window, document, "clarity", "script", "n8x5ekx79q");
    </script>
</head>

<body>


    @if(Session::has('success'))

    <div id="alert" class="position-absolute end-0 alert alert-success alert-dismissible centered-alert fade show alert-margin" role="alert">{{Session::get('success')}} <button type="button" id="alert-close" class="close btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>

    @endif

    @if(Session::has('fail'))

    <div id="alert" class="position-absolute end-0 alert alert-danger alert-dismissible centered-alert fade show alert-margin" role="alert">{{Session::get('fail')}} <button type="button" id="alert-close" class="close btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>

    @endif
    <div id="overlay"></div>
    <div class="scrollable">
        <nav class=" bg-clearlink d-flex justify-content-between">
            <div class="container-fluid">
                <span class="navbar-brand p-1"><img width="150" height="37" src="{{ asset('assets/images/cl_logo.svg') }}" alt="Clearlink Logo"></span>
            </div>
            @yield('button-content')
        </nav>
        <div class="main mb-5">

            @yield('content')
        </div>

        @yield('footer')
    </div>





    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

    <script type="text/javascript" src="{{ asset('assets/js/partner.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>

    @yield('scripts')

</body>

</html>