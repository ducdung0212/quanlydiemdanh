<nav class="navbar navbar-light bg-light px-4">
    <span class="navbar-brand mb-0 h1">Hệ thống điểm danh sinh viên</span>
    <div class="d-flex">
        <span class="me-3">Xin chào, {{ Auth::user()->name ?? 'Guest' }}</span>
        <a href="{{ route('login') }}" class="btn btn-outline-danger btn-sm">Đăng xuất</a>
    </div>
</nav>
