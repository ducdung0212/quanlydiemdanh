<!-- Modal Thêm giảng viên -->
<div class="modal fade" id="addLecturerModal" tabindex="-1" aria-labelledby="addLecturerModalLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLecturerModalLabel">Thêm giảng viên mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addLecturerForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="lecturer_code" class="form-label">Mã Giảng Viên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lecturer_code" name="lecturer_code" required>
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
                            <label for="faculty_code" class="form-label">Mã Khoa <span class="text-danger">*</span></label>
                            <select class="form-select" id="faculty_code" name="faculty_code" required>
                                <option value="" selected disabled>Chọn khoa</option>
                                @foreach ($faculties as $faculty)
                                    @php
                                        $code = $faculty->faculty_code ?? $faculty->code ?? $faculty->id;
                                        $name = $faculty->faculty_name ?? $faculty->name ?? $code;
                                    @endphp
                                    <option value="{{ $code }}">{{ $code }} - {{ $name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="user_id" class="form-label">Người dùng liên kết</label>
                            <input type="number" min="1" class="form-control" id="user_id" name="user_id" placeholder="ID người dùng (tuỳ chọn)">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="phone" class="form-label">Số Điện Thoại</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="example@domain.com">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="btnAddLecturer">
                        <span class="btn-text">Thêm</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
