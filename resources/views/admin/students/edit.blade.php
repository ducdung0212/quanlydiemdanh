<!-- Modal Sửa sinh viên -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStudentModalLabel">Sửa thông tin sinh viên</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editStudentForm">
                <div class="modal-body">
                    <input type="hidden" id="editStudentId" value="{{ $student->student_code }}">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="editStudentCode" class="form-label">Mã Sinh Viên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editStudentCode" name="student_code" required value="{{ $student->student_code }}">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="editFullName" class="form-label">Họ và Tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editFullName" name="full_name" required value="{{ $student->full_name }}">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="editClass" class="form-label">Lớp <span class="text-danger">*</span></label>
                            <select class="form-select" id="editClass" name="class_code" required>
                                <option value="" disabled {{ $student->class_code ? '' : 'selected' }}>Chọn lớp</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->class_code }}" {{ $student->class_code === $class->class_code ? 'selected' : '' }}>
                                        {{ $class->class_code }} - {{ $class->class_name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="editPhone" class="form-label">Số Điện Thoại</label>
                            <input type="tel" class="form-control" id="editPhone" name="phone" value="{{ $student->phone }}">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" value="{{ $student->email }}">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="editAvatarUrl" class="form-label">Link Ảnh đại diện</label>
                            <input type="url" class="form-control" id="editAvatarUrl" name="photo_url" placeholder="https://example.com/image.jpg" value="{{ $student->photo_url }}">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="btnEditStudent">
                        <span class="btn-text">Lưu thay đổi</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
