<!-- Modal Sửa giảng viên -->
<div class="modal fade" id="editLecturerModal" tabindex="-1" aria-labelledby="editLecturerModalLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLecturerModalLabel">Sửa thông tin giảng viên</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editLecturerForm">
                <div class="modal-body">
                    <input type="hidden" id="editLecturerId" value="{{ $lecturer->lecturer_code }}">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="editLecturerCode" class="form-label">Mã Giảng Viên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editLecturerCode" name="lecturer_code" required value="{{ $lecturer->lecturer_code }}">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="editFullName" class="form-label">Họ và Tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editFullName" name="full_name" required value="{{ $lecturer->full_name }}">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="editFacultyCode" class="form-label">Mã Khoa</label>
                            <select class="form-select" id="editFacultyCode" name="faculty_code" required>
                                <option value="" disabled {{ $lecturer->faculty_code ? '' : 'selected' }}>Chọn khoa</option>
                                @foreach ($faculties as $faculty)
                                    @php
                                        $code = $faculty->faculty_code ?? $faculty->code ?? $faculty->id;
                                        $name = $faculty->faculty_name ?? $faculty->name ?? $code;
                                    @endphp
                                    <option value="{{ $code }}" {{ $lecturer->faculty_code === $code ? 'selected' : '' }}>
                                        {{ $code }} - {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="editUserId" class="form-label">Người dùng liên kết</label>
                            <select class="form-select " id="editUserId" name="user_id">
                                <option value="">Không liên kết</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ $lecturer->user_id == $user->id ? 'selected' : '' }}>
                                        {{ $user->id }} - {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="editPhone" class="form-label">Số Điện Thoại</label>
                            <input type="tel" class="form-control" id="editPhone" name="phone" value="{{ $lecturer->phone }}">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" value="{{ $lecturer->email }}" placeholder="example@domain.com">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="btnEditLecturer">
                        <span class="btn-text">Lưu thay đổi</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
