<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Clearlink ISP Admin Portal</title>
  <link rel="icon" href="{{  asset('assets/images/favicon-32x32.png') }}" type="image/x-icon" />
  <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

  <link href="{{asset('assets/css/partner.css') }}" rel="stylesheet">
  <!-- Add these lines in the <head> section -->
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

  <div id="alert" class="position-absolute end-0 alert alert-success alert-dismissible centered-alert fade show" role="alert">{{Session::get('success')}} <button type="button" id="alert-close" class="close btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>

  @endif

  @if(Session::has('fail'))

  <div id="alert" class="position-absolute end-0 alert alert-danger alert-dismissible centered-alert fade show" role="alert">{{Session::get('fail')}} <button type="button" id="alert-close" class="close btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>

  @endif

  <div id="overlay"></div>
  <div class="scrollable">
    <nav class="navbar p-0 m-0">
      <div class="logo_item">
        <div class="d-flex flex-row ms-3 mt-2 justify-content-between">
          <div class="ms-1 ">
            <a href="#" class="mb-1">
              <img width="150" height="33" src="{{ asset('assets/images/cl_logo.svg') }}" alt="Clearlink Logo" /></a>
          </div>
          <div class="me-3 mt-1">
            <i class="fa-solid fa-bars" id="sidebarOpen"></i>
          </div>
        </div>
      </div>
      <div class="navbar_content">
        <i class="bi bi-grid"></i>
        <i class="bx bx-sun" id="darkLight"></i>
        <i class="bx bx-bell"></i>
      </div>
    </nav>
    <div class="wrapper">
      <aside id="sidebar">
        <div class="d-flex flex-row justify-content-between sidebar-sm-header">
          <a href="#" class="mb-1 ms-3">
            <img width="150" height="33" src="{{ asset('assets/images/cl_logo.svg') }}" alt="Clearlink Logo" /></a>
          <i class="fa-solid fa-xmark" id="sidebarClose"></i>
        </div>
        <div class="d-flex">
          <div class="sidebar-logo">
            <a href="#">
              <img class="mt-3 ms-3" width="150" height="33" src="{{ asset('assets/images/cl_logo.svg') }}" alt="Clearlink Logo" /></a>
          </div>
        </div>
        <ul class="sidebar-nav">
          <li class="sidebar-item">
            <ul class="p-0 ms-2">

              <li class="sidebar-item">
                <a href="/admin/subscription" class="{{ Request::is('admin/subscription')   ? 'sidebar-link  text-decoration-none text-primary active-link' : 'sidebar-link  text-decoration-none text-primary' }}"> - Subscriptions</a>
              </li>
              <li class="sidebar-item">
                <a href="/admin/partner" class="{{  Request::is('admin/partner') || Request::is('admin/view-partner*') || Request::is('admin/invite-partner') || Request::is('admin/approve-lead*')  ? 'sidebar-link  text-decoration-none text-primary active-link' : 'sidebar-link  text-decoration-none text-primary' }}"> - Partners</a>
              </li>

              <li class="sidebar-item">
                <a href="/admin/invoice" class="{{ Request::is('admin/invoice')   ? 'sidebar-link  text-decoration-none text-primary active-link' : 'sidebar-link  text-decoration-none text-primary' }}"> - Invoices</a>
              </li>
              <li class="sidebar-item">
                <a href="/admin/support" class="{{ Request::is('admin/support')   ? 'sidebar-link  text-decoration-none text-primary active-link' : 'sidebar-link  text-decoration-none text-primary' }}"> - Support Ticket</a>
              </li>

              <li class="sidebar-item">
                <a href="/admin/terms" class="{{ Route::currentRouteName() === 'admin.terms'  ? 'sidebar-link  text-decoration-none text-primary active-link' : 'sidebar-link  text-decoration-none text-primary' }}"> - Terms Log</a>
              </li>

              <!-- <li class="sidebar-item">
                <a href="/admin/clicks-email" class="{{ Route::currentRouteName() === 'admin.clicks-email'  ? 'sidebar-link  text-decoration-none text-primary active-link' : 'sidebar-link  text-decoration-none text-primary' }}"> - Clicks Email Log</a>
              </li> -->

              <li class="sidebar-item">
                <a href="/admin/leads" class="{{  Request::is('admin/leads') ? 'sidebar-link  text-decoration-none text-primary active-link' : 'sidebar-link  text-decoration-none text-primary' }}"> - New Signups</a>
              </li>
              <li class="sidebar-item">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse" data-bs-target="#auth" aria-expanded="false" aria-controls="auth">
                  <span>- Settings</span><span class="ms-1"><i class="fa-solid fa-angle-down"></i></span>
                </a>

                <ul id="auth" class="ms-5 sidebar-dropdown list-unstyled collapse show" data-bs-parent="#sidebar">

                  <li class="sidebar-item">
                    <a href="/admin" class="{{  Request::is('admin') || Request::is('admin/plan-features*')  ? 'sidebar-link  text-decoration-none text-primary active-link' : 'sidebar-link  text-decoration-none text-primary' }}"> Plans</a>
                  </li>
                  <li class="sidebar-item">
                    <a href="/admin/affiliates" class="{{  Request::is('admin/affiliates') ? 'sidebar-link  text-decoration-none text-primary active-link' : 'sidebar-link  text-decoration-none text-primary' }}"> Affiliates</a>
                  </li>
                  @if(Session::get('role') == 'SuperAdmin')
                  <li class="sidebar-item">
                    <a href="/admin/admins" class="{{ Request::is('admin/admins')   ? 'sidebar-link  text-decoration-none text-primary active-link' : 'sidebar-link  text-decoration-none text-primary' }}"> Admins</a>
                  </li>
                  <li class="sidebar-item">
                    <a href="/admin/api-clients" class="{{ Request::is('admin/api-clients')   ? 'sidebar-link  text-decoration-none text-primary active-link' : 'sidebar-link  text-decoration-none text-primary' }}"> API Clients</a>
                  </li>
                  @endif
                  <li class="sidebar-item">
                    <a href="/admin/profile" class="{{ Request::is('admin/profile')  ? 'sidebar-link  text-decoration-none text-primary active-link' : 'sidebar-link  text-decoration-none text-primary' }}"> Profile</a>
                  </li>

                </ul>
              </li>
            </ul>
          </li>
        </ul>
        <div class="bottom-footer">
          <hr class="line mt-0" />
          <div>
            <a class="sidebar-footer">
              <span class="text-dark fw-bold m-0"><strong>Welcome Admin!</strong></span>
            </a>
            <a class="sidebar-footer">
              <span class="text-dark m-0">{{Session::get('loginAdmin')}}</span>
            </a>
            <a href="/logout" class="sidebar-footer logout text-center p-0 m-0 mb-4 ms-4">
              <span class="btn fw-bold  text-primary ">Logout</span>
            </a>
          </div>

          <div class="footer p-0 m-0 mt-3">
            <a href="#" class="sidebar-footer footer">
              <p class="text-muted small text-wrap p-0 mb-4">
                <span class="text-dark  p-0 mb-4">@ Clearlink Technologies 2024</span>
              </p>
            </a>
          </div>
        </div>
      </aside>
      <div class="sm-footer fw-bold">
        Signed in as {{Session::get('loginAdmin')}}
      </div>



      <div id="content" style="box-sizing: border-box; margin-left:300px; width:100%" class="p-3">

        @yield('content')

      </div>

      <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

      <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>

      <script type="text/javascript" src="{{ asset('assets/js/partner.js') }}"></script>

      <script type="text/javascript" src="{{ asset('assets/js/admin.js') }}"></script>


      <script>
        // Initialize tooltips
        $(document).ready(function() {
          $('[data-toggle="tooltip"]').tooltip();
        });
      </script>
      <script>
        $(document).ready(function() {

        });
      </script>

      @yield('scripts')

</body>

</html>