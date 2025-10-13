<!-- Modal Thêm sinh viên -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStudentModalLabel">Thêm sinh viên mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addStudentForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="student_code" class="form-label">Mã Sinh Viên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="student_code" name="student_code" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="full_name" class="form-label">Họ và Tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="class" class="form-label">Lớp <span class="text-danger">*</span></label>
                            <select class="form-select" id="class" name="class_code" required>
                                <option value="" selected disabled>Chọn lớp</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->class_code }}">
                                        {{ $class->class_code }} - {{ $class->class_name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="phone" class="form-label">Số Điện Thoại</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="avatar_url" class="form-label">Link Ảnh đại diện</label>
                            <input type="url" class="form-control" id="avatar_url" name="photo_url" placeholder="https://example.com/image.jpg">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="btnAddStudent">
                        <span class="btn-text">Thêm</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
