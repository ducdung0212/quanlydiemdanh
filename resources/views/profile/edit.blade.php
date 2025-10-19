@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h2 class="mb-4">Cập nhật hồ sơ</h2>

        @if (session('status') === 'profile-updated')
            <div class="alert alert-success" role="alert">
                Cập nhật thông tin thành công.
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="mb-5">
            @csrf
            @method('PATCH')

            <div class="mb-3">
                <label for="name" class="form-label">Họ và tên</label>
                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                    value="{{ old('name', $user->name) }}" required autofocus>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Địa chỉ email</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                    value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
        </form>

        <hr>

        <div class="mt-4">
            <h3 class="h5 text-danger">Xóa tài khoản</h3>
            <p>Hành động này không thể hoàn tác. Vui lòng nhập mật khẩu để xác nhận.</p>

            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')

                <div class="mb-3">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                        name="password" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa tài khoản?')">
                    Xóa tài khoản
                </button>
            </form>
        </div>
    </div>
@endsection
