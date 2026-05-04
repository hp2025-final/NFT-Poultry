<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'NF Dev')</title>
  <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  @yield('styles')
</head>
<body>

{{-- ===== Top Navbar ===== --}}
<nav class="navbar navbar-dark navbar-main sticky-top">
  <div class="container-fluid">
    <div class="d-flex align-items-center gap-2">
      @auth
      <button class="btn btn-outline-light btn-sm d-lg-none me-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-label="Toggle menu">
        <i class="bi bi-list fs-5"></i>
      </button>
      @endauth
      <a class="navbar-brand mb-0" href="{{ route('dashboard') ?? '/' }}">
        <i class="bi bi-box-seam me-1"></i>NF Dev
      </a>
    </div>
    @auth
    <div class="d-flex align-items-center gap-2">
      <span class="navbar-text d-none d-sm-inline">
        <i class="bi bi-person-circle me-1"></i><strong>{{ auth()->user()->username }}</strong>
      </span>
      <form method="post" action="{{ route('logout') }}" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-outline-light btn-logout">
          <i class="bi bi-box-arrow-right me-1"></i>Logout
        </button>
      </form>
    </div>
    @endauth
  </div>
</nav>

@auth
{{-- ===== Main App Wrapper ===== --}}
<div class="app-wrapper">

  {{-- ===== Desktop Sidebar (visible lg+) ===== --}}
  <aside class="sidebar-desktop d-none d-lg-block">
    @include('layouts._sidebar_links')
  </aside>

  {{-- ===== Mobile Offcanvas Sidebar ===== --}}
  <div class="offcanvas offcanvas-start offcanvas-sidebar d-lg-none" tabindex="-1" id="mobileSidebar">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title"><i class="bi bi-box-seam me-2"></i>NF Dev</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
      @include('layouts._sidebar_links')
    </div>
  </div>

  {{-- ===== Main Content ===== --}}
  <main class="main-content">
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif
    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @yield('content')
  </main>
</div>
@else
{{-- ===== Guest Layout (Login page) ===== --}}
<div class="container mt-5">
  @yield('content')
</div>
@endauth

<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Init date pickers
    if (window.flatpickr) {
      document.querySelectorAll('.js-date').forEach(function(el){
        flatpickr(el, {
          dateFormat: 'Y-m-d',
          altInput: true,
          altFormat: 'd-m-y',
          allowInput: true
        });
      });
    }

    // Auto-close mobile sidebar on link click
    const mobileSidebar = document.getElementById('mobileSidebar');
    if (mobileSidebar) {
      mobileSidebar.querySelectorAll('.sidebar-link').forEach(function(link) {
        link.addEventListener('click', function() {
          var offcanvas = bootstrap.Offcanvas.getInstance(mobileSidebar);
          if (offcanvas) offcanvas.hide();
        });
      });
    }

    // Auto-dismiss alerts after 5 seconds
    document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
      setTimeout(function() {
        var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
        bsAlert.close();
      }, 5000);
    });
  });
</script>
@yield('scripts')
</body>
</html>
