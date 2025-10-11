<!-- Modal Sửa tài khoản -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Sửa thông tin tài khoản</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm">
                <div class="modal-body">
                    <input type="hidden" id="editUserId" value="{{ $user->id }}">
                    <div class="mb-4">
                        <label for="editName" class="form-label">Tên *</label>
                        <input type="text" class="form-control" id="editName" name="name" required value="{{ $user->name }}">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-4">
                        <label for="editEmail" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="editEmail" name="email" required value="{{ $user->email }}">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-4">
                        <label for="editPassword" class="form-label">Mật khẩu mới (Để trống nếu không đổi)</label>
                        <input type="password" class="form-control" id="editPassword" name="password">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-4">
                        <label for="editPasswordConfirmation" class="form-label">Xác nhận mật khẩu mới</label>
                        <input type="password" class="form-control" id="editPasswordConfirmation" name="password_confirmation">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-text">Lưu thay đổi</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
