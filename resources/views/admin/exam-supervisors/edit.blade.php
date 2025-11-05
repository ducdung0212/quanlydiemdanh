<!-- Modal Sửa giám thị -->
<div class="modal fade" id="editSupervisorModal" tabindex="-1" aria-labelledby="editSupervisorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSupervisorModalLabel">Sửa Giám Thị</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSupervisorForm">
                <div class="modal-body">
                    <input type="hidden" id="editSupervisorId" value="{{ $supervisor->id }}">

                    <div class="mb-3">
                        <label for="editExamSchedule" class="form-label">Mã Ca Thi</label>
                        <input type="text" class="form-control" id="editExamSchedule" value="{{ $supervisor->exam_schedule_id }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="editLecturerCode" class="form-label">Giảng viên <span class="text-danger">*</span></label>
                        <select id="editLecturerCode" name="lecturer_code" class="form-control" required>
                            <option value="">-- Chọn giảng viên --</option>
                            @foreach($lecturers as $lec)
                                <option value="{{ $lec->lecturer_code }}" {{ $lec->lecturer_code === $supervisor->lecturer_code ? 'selected' : '' }}>
                                    {{ $lec->full_name }} ({{ $lec->lecturer_code }})
                                </option>
                            @endforeach
                        </select>
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
