@extends('layouts.app')

@section('content')
  <div class="main-panel container-fluid py-4 profile-page">
    

      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <strong>Chỉnh sửa hồ sơ</strong>
          </div>
          <div class="card-body">
            @if (session('status') === 'profile-updated')
              <div class="alert alert-success" role="alert">
                Cập nhật thông tin thành công.
              </div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}">
              @csrf
              @method('PATCH')

              <div class="row mb-3">
                <label for="name" class="col-sm-3 col-form-label">Họ và tên</label>
                <div class="col-sm-9">
                  <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                    value="{{ old('name', $user->name) }}" required autofocus>
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="row mb-3">
                <label for="email" class="col-sm-3 col-form-label">Email</label>
                <div class="col-sm-9">
                  <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                    value="{{ old('email', $user->email) }}" required>
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <hr>

              <div class="row mb-3">
                <label for="password" class="col-sm-3 col-form-label">Mật khẩu mới</label>
                <div class="col-sm-9">
                  <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror">
                  <div class="form-text">Để trống nếu bạn không muốn thay đổi mật khẩu.</div>
                  @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="row mb-3">
                <label for="password_confirmation" class="col-sm-3 col-form-label">Xác nhận mật khẩu</label>
                <div class="col-sm-9">
                  <input id="password_confirmation" name="password_confirmation" type="password" class="form-control">
                </div>
              </div>

              <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection