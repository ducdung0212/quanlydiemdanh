<!-- Modal Thêm tài khoản -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Thêm tài khoản mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addUserForm">
                <div class="modal-body">
                    <div class="mb-4">
                        <label for="name" class="form-label">Tên *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-4">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Mật khẩu *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Xác nhận mật khẩu *</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-4">
                        <label for="role" class="form-label">Role *</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="">Chọn role</option>
                            <option value="admin">Admin</option>
                            <option value="lecturer" selected>Lecturer</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-text">Thêm</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
