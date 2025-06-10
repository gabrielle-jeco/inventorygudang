<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Inventory Gudang</title>
  <link rel="icon" type="image/jpg" href="{{ asset('logo.jpg') }}">

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">

  <!-- CSS Libraries -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">

  <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
  
  <!-- Select2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <!-- Datatable Jquery -->
  <link rel="stylesheet" href="//cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.4.1/css/dataTables.dateTime.min.css">

  <style>
    :root {
      --primary-color: #6777ef;
      --secondary-color: #34395e;
      --accent-color: #47c363;
      --danger-color: #fc544b;
      --warning-color: #ffa426;
    }

    body {
      background: #f4f6f9;
    }

    #background-canvas {
      position: fixed;
      top: 0;
      left: 0;
      z-index: -1;
      pointer-events: none;
    }

    .main-sidebar {
      background: linear-gradient(135deg, var(--secondary-color), #2c3146);
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .main-sidebar .sidebar-brand {
      padding: 1rem;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(255,255,255,0.1);
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .main-sidebar .sidebar-brand img {
      height: 35px;
      margin-right: 10px;
      border-radius: 5px;
    }

    .main-sidebar .sidebar-brand a {
      color: #fff;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .navbar-bg {
      background: linear-gradient(135deg, var(--primary-color), #4e5ac7);
      height: 70px;
    }

    .navbar {
      background: transparent;
      height: 70px;
    }

    .card {
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      border: none;
      overflow: hidden;
    }

    .card .card-header {
      background: linear-gradient(135deg, var(--primary-color), #4e5ac7);
      color: white;
      border-bottom: none;
      padding: 1.5rem;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }

    .btn {
      border-radius: 5px;
      transition: all 0.3s ease;
      text-transform: uppercase;
      font-weight: 600;
      letter-spacing: 0.5px;
      padding: 0.6rem 1.2rem;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary-color), #4e5ac7);
      border: none;
    }

    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .nav-link {
      transition: all 0.3s ease;
      border-radius: 5px;
      margin: 2px 0;
    }

    .nav-link:hover {
      background: rgba(255,255,255,0.1);
    }

    .nav-link.active {
      background: var(--primary-color) !important;
      color: white !important;
    }

    .table {
      border-radius: 10px;
      overflow: hidden;
    }

    .table thead th {
      background: var(--primary-color);
      color: white;
      border: none;
      padding: 1rem;
    }

    .table tbody tr:hover {
      background: rgba(103, 119, 239, 0.05);
    }

    .dropdown-menu {
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      border: none;
      padding: 0.5rem;
    }

    .dropdown-item {
      border-radius: 5px;
      padding: 0.5rem 1rem;
      transition: all 0.3s ease;
    }

    .dropdown-item:hover {
      background: rgba(103, 119, 239, 0.1);
    }

    .search-element input {
      border-radius: 20px;
      padding-left: 1rem;
      border: none;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .search-element button {
      border-radius: 20px;
      padding: 0.4rem 1rem;
    }

    .animate-fade-in {
      animation: fadeIn 0.5s ease-in;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Add new styles for the scanner button */
    .scanner-btn {
      position: fixed;
      bottom: 30px;
      right: 30px;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary-color), #4e5ac7);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      transition: all 0.3s ease;
      z-index: 1000;
      border: none;
    }

    .scanner-btn:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 15px rgba(0,0,0,0.2);
    }

    .scanner-btn i {
      font-size: 24px;
    }

    .scanner-tooltip {
      position: absolute;
      background: rgba(0,0,0,0.8);
      color: white;
      padding: 5px 10px;
      border-radius: 5px;
      font-size: 12px;
      bottom: 70px;
      right: 0;
      white-space: nowrap;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .scanner-btn:hover .scanner-tooltip {
      opacity: 1;
    }
  </style>

  <!-- Start GA -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=UA-94034622-3"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'UA-94034622-3');
  </script>
<!-- /END GA --></head>

<body>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>
      <nav class="navbar navbar-expand-lg main-navbar">
        <form class="form-inline mr-auto">
          <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
            <li><a href="#" data-toggle="search" class="nav-link nav-link-lg d-sm-none"><i class="fas fa-search"></i></a></li>
          </ul>
          <div class="search-element">
            <input class="form-control" type="search" placeholder="Search" aria-label="Search" data-width="250">
            <button class="btn" type="submit"><i class="fas fa-search"></i></button>
            <div class="search-backdrop"></div>
          </div>
        </form>
        <ul class="navbar-nav navbar-right">
          

          <li class="dropdown"><a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
            <img alt="image" src="assets/img/avatar/avatar-1.png" class="rounded-circle mr-1">
            <div class="d-sm-none d-lg-inline-block">Hi, {{ auth()->user()->name }}</div></a>
            <div class="dropdown-menu dropdown-menu-right">
              <a href="/ubah-password" class="dropdown-item has-icon">
                <i class="fa fa-sharp fa-solid fa-lock"></i> Ubah Password
              </a>
              <div class="dropdown-divider"></div>
                  <a class="dropdown-item" href="{{ route('logout') }}"
                    onclick="event.preventDefault();
                                Swal.fire({
                                    title: 'Konfirmasi Keluar',
                                    text: 'Apakah Anda yakin ingin keluar?',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#d33',
                                    confirmButtonText: 'Ya, Keluar!'
                                  }).then((result) => {
                                    if (result.isConfirmed) {
                                      document.getElementById('logout-form').submit();
                                    }
                                  });">
                               <i class="fas fa-sign-out-alt"></i> {{ __('Keluar') }}
                              </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                  </a>
            </div>
          </li>
        </ul>
      </nav>
      <div class="main-sidebar sidebar-style-2">
        <aside id="sidebar-wrapper">

          <div class="sidebar-brand">
            <img src="{{ asset('logo.jpg') }}" alt="Logo" class="animate__animated animate__fadeIn">
            <a href="/" class="animate__animated animate__fadeIn">INVENTORY GUDANG</a>
          </div>

          <ul class="sidebar-menu"> 
            @if (auth()->user()->role->role === 'kepala gudang')
              <li class="sidebar-item">
                <a class="nav-link {{ Request::is('/') || Request::is('dashboard') ? 'active' : '' }}" href="/">
                  <i class="fas fa-fire"></i> <span class="align-middle">Dashboard</span>
                </a>
              </li>
  
              <li class="menu-header">LAPORAN</li>
              <li><a class="nav-link {{ Request::is('laporan-stok') ? 'active' : '' }}" href="laporan-stok"><i class="fa fa-sharp fa-reguler fa-file"></i><span>Stok</span></a></li>
              <li><a class="nav-link {{ Request::is('laporan-barang-masuk') ? 'active' : '' }}" href="laporan-barang-masuk"><i class="fa fa-regular fa-file-import"></i><span>Barang Masuk</span></a></li>
              <li><a class="nav-link {{ Request::is('laporan-barang-keluar') ? 'active' : '' }}" href="laporan-barang-keluar"><i class="fa fa-sharp fa-regular fa-file-export"></i><span>Barang Keluar</span></a></li>
            
              <li class="menu-header">MANAJEMEN USER</li>
              <li><a class="nav-link {{ Request::is('aktivitas-user') ? 'active' : '' }}" href="aktivitas-user"><i class="fa fa-solid fa-list"></i><span>Aktivitas User</span></a></li>
            @endif

            @if (auth()->user()->role->role === 'superadmin')
              <li class="sidebar-item">
                <a class="nav-link {{ Request::is('/') || Request::is('dashboard') ? 'active' : '' }}" href="/">
                  <i class="fas fa-fire"></i> <span class="align-middle">Dashboard</span>
                </a>
              </li>

              <li class="menu-header">DATA MASTER</li>
                <li class="dropdown">
                  <a href="#" class="nav-link has-dropdown {{ Request::is('barang') || Request::is('jenis-barang') || Request::is('satuan-barang') ? 'active' : '' }}" data-toggle="dropdown"><i class="fas fa-thin fa-cubes"></i><span>Data Barang</span></a>
                  <ul class="dropdown-menu">
                    <li><a class="nav-link {{ Request::is('barang') ? 'active' : '' }}" href="/barang"><i class="fa fa-solid fa-circle fa-xs"></i> Nama Barang</a></li>
                    <li><a class="nav-link {{ Request::is('jenis-barang') ? 'active' : '' }}" href="/jenis-barang"><i class="fa fa-solid fa-circle fa-xs"></i> Jenis</a></li>
                    <li><a class="nav-link {{ Request::is('satuan-barang') ? 'active' : '' }}" href="/satuan-barang"><i class="fa fa-solid fa-circle fa-xs"></i> Satuan</a></li>
                  </ul>
                </li>
                <li class="dropdown">
                  <a href="#" class="nav-link has-dropdown {{ Request::is('supplier')  || Request::is('customer') ? 'active' : '' }}" data-toggle="dropdown"><i class="fa fa-sharp fa-solid fa-building"></i><span>Perusahaan</span></a>
                  <ul class="dropdown-menu">
                    <li><a class="nav-link {{ Request::is('supplier') ? 'active' : '' }}" href="/supplier"><i class="fa fa-solid fa-circle fa-xs"></i> Supplier</a></li>
                    <li><a class="nav-link {{ Request::is('customer') ? 'active' : '' }}" href="/customer"><i class="fa fa-solid fa-circle fa-xs"></i> Customer</a></li>
                  </ul>
                </li>

              <li class="menu-header">TRANSAKSI</li>
              <li><a class="nav-link {{ Request::is('barang-masuk') ? 'active' : '' }}" href="barang-masuk"><i class="fa fa-solid fa-arrow-right"></i><span>Barang Masuk</span></a></li>
              <li><a class="nav-link {{ Request::is('barang-keluar') ? 'active' : '' }}" href="barang-keluar"><i class="fa fa-sharp fa-solid fa-arrow-left"></i> <span>Barang Keluar</span></a></li>
            
              <li class="menu-header">ANPR</li>
              <li><a class="nav-link {{ Request::is('anpr') ? 'active' : '' }}" href="{{ route('anpr.index') }}"><i class="fa fa-solid fa-camera"></i><span>Plate Recognition</span></a></li>
            
              <li class="menu-header">LAPORAN</li>
              <li><a class="nav-link {{ Request::is('laporan-stok') ? 'active' : '' }}" href="laporan-stok"><i class="fa fa-sharp fa-reguler fa-file"></i><span>Stok</span></a></li>
              <li><a class="nav-link {{ Request::is('laporan-barang-masuk') ? 'active' : '' }}" href="laporan-barang-masuk"><i class="fa fa-regular fa-file-import"></i><span>Barang Masuk</span></a></li>
              <li><a class="nav-link {{ Request::is('laporan-barang-keluar') ? 'active' : '' }}" href="laporan-barang-keluar"><i class="fa fa-sharp fa-regular fa-file-export"></i><span>Barang Keluar</span></a></li>
              
              <li class="menu-header">MANAJEMEN USER</li>
              <li><a class="nav-link {{ Request::is('data-pengguna') ? 'active' : '' }}" href="data-pengguna"><i class="fa fa-solid fa-users"></i><span>Data Pengguna</span></a></li>
              <li><a class="nav-link {{ Request::is('hak-akses') ? 'active' : '' }}" href="hak-akses"><i class="fa fa-solid fa-user-lock"></i><span>Hak Akses/Role</span></a></li>
              <li><a class="nav-link {{ Request::is('aktivitas-user') ? 'active' : '' }}" href="aktivitas-user"><i class="fa fa-solid fa-list"></i><span>Aktivitas User</span></a></li>
              
              <li class="menu-header">MANAJEMEN KENDARAAN</li>
              <li><a class="nav-link {{ Request::is('vehicles') ? 'active' : '' }}" href="{{ route('vehicles.index') }}"><i class="fa fa-solid fa-truck"></i><span>Data Kendaraan</span></a></li>
        
            @endif
            
            @if (auth()->user()->role->role === 'admin gudang')
            <li class="sidebar-item">
              <a class="sidebar-link nav-link {{ Request::is('/') || Request::is('dashboard') ? 'active' : '' }}" href="/">
                <i class="fas fa-fire"></i> <span class="align-middle">Dashboard</span>
              </a>
            </li>

              <li class="menu-header">DATA MASTER</li>
              <li class="dropdown">
                <a href="#" class="nav-link has-dropdown {{ Request::is('barang') || Request::is('jenis-barang') || Request::is('satuan-barang') ? 'active' : '' }}" data-toggle="dropdown"><i class="fas fa-thin fa-cubes"></i><span>Data Barang</span></a>
                <ul class="dropdown-menu">
                  <li><a class="nav-link {{ Request::is('barang') ? 'active' : '' }}" href="/barang"><i class="fa fa-solid fa-circle fa-xs"></i> Nama Barang</a></li>
                  <li><a class="nav-link {{ Request::is('jenis-barang') ? 'active' : '' }}" href="/jenis-barang"><i class="fa fa-solid fa-circle fa-xs"></i> Jenis</a></li>
                  <li><a class="nav-link {{ Request::is('satuan-barang') ? 'active' : '' }}" href="/satuan-barang"><i class="fa fa-solid fa-circle fa-xs"></i> Satuan</a></li>
                </ul>
              </li>
              <li class="dropdown">
                <a href="#" class="nav-link has-dropdown {{ Request::is('supplier')  || Request::is('customer') ? 'active' : '' }}" data-toggle="dropdown"><i class="fa fa-sharp fa-solid fa-building"></i><span>Perusahaan</span></a>
                <ul class="dropdown-menu">
                  <li><a class="nav-link {{ Request::is('supplier') ? 'active' : '' }}" href="/supplier"><i class="fa fa-solid fa-circle fa-xs"></i> Supplier</a></li>
                  <li><a class="nav-link {{ Request::is('customer') ? 'active' : '' }}" href="/customer"><i class="fa fa-solid fa-circle fa-xs"></i> Customer</a></li>
                </ul>
              </li>

              <li class="menu-header">TRANSAKSI</li>
              <li><a class="nav-link {{ Request::is('barang-masuk') ? 'active' : '' }}" href="barang-masuk"><i class="fa fa-solid fa-arrow-right"></i><span>Barang Masuk</span></a></li>
              <li><a class="nav-link {{ Request::is('barang-keluar') ? 'active' : '' }}" href="barang-keluar"><i class="fa fa-sharp fa-solid fa-arrow-left"></i> <span>Barang Keluar</span></a></li>
              
              <li class="menu-header">ANPR</li>
              <li><a class="nav-link {{ Request::is('anpr') ? 'active' : '' }}" href="{{ route('anpr.index') }}"><i class="fa fa-solid fa-camera"></i><span>Plate Recognition</span></a></li>
            
              <li class="menu-header">LAPORAN</li>
              <li><a class="nav-link {{ Request::is('laporan-stok') ? 'active' : '' }}" href="laporan-stok"><i class="fa fa-sharp fa-reguler fa-file"></i><span>Stok</span></a></li>
              <li><a class="nav-link {{ Request::is('laporan-barang-masuk') ? 'active' : '' }}" href="laporan-barang-masuk"><i class="fa fa-regular fa-file-import"></i><span>Barang Masuk</span></a></li>
              <li><a class="nav-link {{ Request::is('laporan-barang-keluar') ? 'active' : '' }}" href="laporan-barang-keluar"><i class="fa fa-sharp fa-regular fa-file-export"></i><span>Barang Keluar</span></a></li>
              
            @endif
          </ul>

        </aside>
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">

            @yield('content')
          <div class="section-body">
          </div>
        </section>

        @if(Request::is('barang-masuk') || Request::is('barang-keluar'))
        <a href="{{ route('barcode.index') }}" class="scanner-btn">
          <i class="fa fa-qrcode"></i>
          <span class="scanner-tooltip">Scan QR Code</span>
        </a>
        @endif
      </div>
      <footer class="main-footer">
        <div class="footer-left">
          Copyright &copy; 2023 
        </div>
        <div class="footer-right">
          
        </div>
      </footer>
    </div>
  </div>


  
  <!-- General JS Scripts -->
  <script src="assets/modules/jquery.min.js"></script>
  <script src="assets/modules/popper.js"></script>
  <script src="assets/modules/tooltip.js"></script>
  <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
  <script src="assets/modules/nicescroll/jquery.nicescroll.min.js"></script>
  <script src="assets/modules/moment.min.js"></script>
  <script src="assets/js/stisla.js"></script>

  <!-- JS Libraies -->
  
  <!-- Select2 Jquery -->
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js" integrity="sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0=" crossorigin="anonymous"></script>

  <!-- Page Specific JS File -->
  
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>
  <script src="assets/js/custom-animations.js"></script>

  <!-- Datatables Jquery -->
  <script type="text/javascript" src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

  <!-- Sweet Alert -->
  @include('sweetalert::alert')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

  <!-- Day Js Format -->
  <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>

  
  @stack('scripts')

  
  <script>
    $(document).ready(function() {
      var currentPath = window.location.pathname;
  
      $('.nav-link a[href="' + currentPath + '"]').addClass('active');
    });
  </script>
  
</body>
</html>
