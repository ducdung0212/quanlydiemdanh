<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
    <title>Điểm danh sinh viên STU</title>
    <meta charset="utf-8">
    <meta name="author" content="themesflat.com">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/animate.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/animation.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap-select.min.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('font/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('icon/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">
    <link rel="apple-touch-icon-precomposed" href="{{ asset('images/favicon.ico') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/sweetalert.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/custom.css') }}">
</head>

<div class="header-dashboard">
    <div class="wrap">
        <div class="button-show-hide" style="cursor: pointer; padding: 10px; display: flex; align-items: center;">
            <i class="icon-menu-left" style="font-size: 24px;"></i>
        </div>
        <div class="header-grid" style="display: flex; align-items: center; gap: 5px; margin-left: auto;">
            <div class="popup-wrap user type-header">
                <div class="dropdown">
                    <a class="dropdown-toggle d-flex align-items-center" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="header-user wg-user d-flex align-items-center">
                            <span class="image me-2">
                                <img src="{{ asset('images/avatar/user-1.png') }}" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                            </span>
                            <span class="flex flex-column text-start">
                                <span class="body-title mb-0">{{ Auth::user()->name }}</span>
                                <span class="text-tiny">{{ Auth::user()->role }}</span>
                            </span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuLink">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">Xem hồ sơ</a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" id="logoutFormDropdown">
                                @csrf
                                <button type="submit" class="dropdown-item">Đăng xuất</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

     
            
        </div>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('openProfileEdit');
        if (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const modalEl = document.getElementById('profileEditModal');
                if (modalEl) {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                } else {
                    // fallback: navigate to profile page
                    window.location.href = '{{ route('profile.edit') }}';
                }
            });
        }
    });
</script>