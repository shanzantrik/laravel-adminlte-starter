<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name') }}</title>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    @vite(['resources/css/app.css'])
    @stack('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                @permission('dashboard.read')
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('admin.dashboard.index') }}" class="nav-link">{{ __('Dashboard') }}</a>
                </li>
                @endpermission
            </ul>
            <ul class="navbar-nav ml-auto">
                <!-- Navbar Search -->
                <li class="nav-item">
                    <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                        <i class="fas fa-search"></i>
                    </a>
                    <div class="navbar-search-block">
                        <form class="form-inline">
                            <div class="input-group input-group-sm">
                                <input class="form-control form-control-navbar" type="search" placeholder="Search"
                                    aria-label="Search">
                                <div class="input-group-append">
                                    <button class="btn btn-navbar" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
            </ul>
        </nav>

        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="javascript:void(0);" class="brand-link">
                {{-- <span class="brand-image">
                    {{ __('Foo') }}
                </span> --}}
                <span class="brand-text font-weight-light">{{ config('app.name') }}</span>
            </a>

            <div class="sidebar">
                <!-- Sidebar user panel -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="{{ asset('images/default_avatar.jpg') }}" class="img-circle elevation-2"
                            alt="User Image">
                    </div>
                    <div class="info">
                        <a href="{{ route('profile.index') }}" class="d-block">{{ auth()->user()->name }}</a>
                    </div>
                </div>

                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        @permission('dashboard.read')
                        <li class="nav-item">
                            <a href="{{ route('admin.dashboard.index') }}"
                                class="nav-link {{ Request::routeIs('admin.dashboard.index') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>{{ __('Dashboard') }}</p>
                            </a>
                        </li>
                        @endpermission

                        <!-- Administration Dropdown -->
                        <li
                            class="nav-item has-treeview {{ Request::is('admin/roles*') || Request::is('admin/permissions*') || Request::is('admin/users*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>
                                    {{ __('Administration') }}
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @permission('roles.read')
                                <li class="nav-item">
                                    <a href="{{ route('admin.roles.index') }}"
                                        class="nav-link {{ Request::is('admin/roles*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('Roles') }}</p>
                                    </a>
                                </li>
                                @endpermission

                                @permission('permissions.read')
                                <li class="nav-item">
                                    <a href="{{ route('admin.permissions.index') }}"
                                        class="nav-link {{ Request::is('admin/permissions*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('Permissions') }}</p>
                                    </a>
                                </li>
                                @endpermission

                                @permission('users.read')
                                <li class="nav-item">
                                    <a href="{{ route('admin.users.index') }}"
                                        class="nav-link {{ Request::is('admin/users*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('Users') }}</p>
                                    </a>
                                </li>
                                @endpermission
                            </ul>
                        </li>

                        <!-- Receipt Dropdown -->
                        <li
                            class="nav-item has-treeview {{ Request::is('admin/customers*') || Request::is('admin/booking-advances*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-file-invoice"></i>
                                <p>
                                    {{ __('Receipt') }}
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li
                                    class="nav-item has-treeview {{ Request::is('admin/customers*') || Request::is('admin/booking-advances*') ? 'menu-open' : '' }}">
                                    <a href="#" class="nav-link active">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            {{ __('Sales') }}
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        @permission('customers.read')
                                        <li class="nav-item">
                                            <a href="{{ route('admin.customers.index') }}"
                                                class="nav-link {{ Request::is('admin/customers*') ? 'active' : '' }}">
                                                <i class="far fa-user-circle nav-icon"></i>
                                                <p>{{ __('Customers') }}</p>
                                            </a>
                                        </li>
                                        @endpermission

                                        @permission('booking_advances.read')
                                        <li class="nav-item">
                                            <a href="{{ route('admin.booking-advances.create') }}"
                                                class="nav-link {{ Request::is('admin/booking-advances*') ? 'active' : '' }}">
                                                <i class="far fa-credit-card nav-icon"></i>
                                                <p>{{ __('Booking Advance') }}</p>
                                            </a>
                                        </li>
                                        @endpermission

                                        <li class="nav-item">
                                            <a href="{{ route('admin.new-vehicle-sales.index') }}"
                                                class="nav-link {{ request()->routeIs('admin.new-vehicle-sales.*') ? 'active' : '' }}">
                                                <i class="nav-icon fas fa-car"></i>
                                                <p>New Vehicle Sales</p>
                                            </a>
                                        </li>

                                        <li class="nav-item">
                                            <a href="{{ route('admin.vas-invoices.index') }}"
                                                class="nav-link {{ request()->routeIs('admin.vas-invoices.*') ? 'active' : '' }}">
                                                <i class="fa fa-file nav-icon"></i>
                                                <p>{{ __('VAS Invoice') }}</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <!-- Services Dropdown -->
                                <li class="nav-item has-treeview">
                                    <a href="#" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            {{ __('Services') }}
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <a href="{{ route('admin.job-advances.index') }}"
                                                class="nav-link {{ request()->routeIs('admin.job-advances.*') ? 'active' : '' }}">
                                                <i class="fas fa-tools nav-icon"></i>
                                                <p>{{ __('Job Advance') }}</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#" class="nav-link">
                                                <i class="fas fa-file-invoice-dollar nav-icon"></i>
                                                <p>{{ __('Service Bill') }}</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#" class="nav-link">
                                                <i class="fas fa-cash-register nav-icon"></i>
                                                <p>{{ __('Counter Sales') }}</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <!-- Used Car Dropdown -->
                                <li class="nav-item has-treeview">
                                    <a href="#" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            {{ __('Used Cars') }}
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <a href="#" class="nav-link">
                                                <i class="fas fa-tools nav-icon"></i>
                                                <p>{{ __('Advance') }}</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#" class="nav-link">
                                                <i class="fa fa-car nav-icon"></i>
                                                <p>{{ __('Used Car Sales') }}</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <!-- Insurance Dropdown -->
                                <li class="nav-item has-treeview">
                                    <a href="#" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            {{ __('Insurance') }}
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <a href="#" class="nav-link">
                                                <i class="fas fa-tools nav-icon"></i>
                                                <p>{{ __('Advance') }}</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#" class="nav-link">
                                                <i class="fas fa-file-invoice-dollar nav-icon"></i>
                                                <p>{{ __('Policy Issued') }}</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('Extended Warranty') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('Credit Recovery') }}</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Payments -->
                        <li class="nav-item">
                            <a href="{{ route('paymentsmain.create') }}" class="nav-link">
                                <i class="nav-icon fas fa-money-bill-wave"></i>
                                <p>{{ __('Payments') }}</p>
                            </a>
                        </li>

                        <!-- Transfers -->
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-exchange-alt"></i>
                                <p>{{ __('Transfers') }}</p>
                            </a>
                        </li>

                        <!-- Reports -->
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>{{ __('Reports') }}</p>
                            </a>
                        </li>
                    </ul>
                </nav>

                <!-- Bottom Navigation -->
                <nav class="mt-auto">
                    <ul class="nav nav-pills nav-sidebar flex-column">

                        <!-- Logout -->
                        <li class="nav-item">
                            <a href="javascript:void(0);" id="logout-button" class="nav-link text-danger">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>{{ __('Logout') }}</p>
                            </a>
                            <form id="logout-form" class="d-none" action="{{ route('logout') }}" method="POST">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <div class="content-wrapper">
            <div class="content">
                <div class="container-fluid">
                    @yield('main')
                </div>
            </div>
        </div>

        <footer class="main-footer">
            <strong>Copyright &copy; 2024 <a href="{{ route('admin.dashboard.index') }}">{{ config('app.name')
                    }}</a>.</strong>
            <span>{{ __('All rights reserved.') }}</span>
        </footer>
    </div>

    <script>
        window.jQuery = null;
        window.$ = null;
    </script>

    @vite(['resources/js/app.js'])
    @stack('scripts')

    <!-- Essential Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

</body>

</html>
