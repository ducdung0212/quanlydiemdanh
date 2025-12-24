<!-- Modal Thêm/Sửa Ca Thi -->
<div class="modal fade" id="examScheduleFormModal" tabindex="-1" aria-labelledby="examScheduleFormModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="examScheduleFormModalLabel">Thêm Ca Thi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="examScheduleForm">
                <div class="modal-body">
                    <input type="hidden" id="examScheduleId" name="id">

                    <div class="row">
                        <!-- Môn học -->
                        <div class="col-md-6 mb-3">
                            <label for="subject_code" class="form-label fs-4">Môn học <span
                                    class="text-danger">*</span></label>
                            <select class="form-select-xl" id="subject_code" name="subject_code" required>
                                <option value="">-- Chọn môn học --</option>
                                @foreach (\App\Models\Subject::orderBy('name')->get() as $subject)
                                    <option value="{{ $subject->subject_code }}">{{ $subject->subject_code }} -
                                        {{ $subject->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="error_subject_code"></div>
                        </div>

                        <!-- Phòng thi -->
                        <div class="col-md-6 mb-3">
                            <label for="room" class="form-label fs-4">Phòng thi <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="room" name="room" required
                                maxlength="50">
                            <div class="invalid-feedback" id="error_room"></div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Ngày thi -->
                        <div class="col-md-4 mb-3">
                            <label for="exam_date" class="form-label fs-4">Ngày thi <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="exam_date" name="exam_date" required>
                            <div class="invalid-feedback" id="error_exam_date"></div>
                        </div>

                        <!-- Giờ thi -->
                        <div class="col-md-4 mb-3">
                            <label for="exam_time" class="form-label fs-4">Giờ thi <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="exam_time" name="exam_time" required
                                step="1">
                            <div class="invalid-feedback" id="error_exam_time"></div>
                        </div>

                        <!-- Thời lượng -->
                        <div class="col-md-4 mb-3">
                            <label for="duration" class="form-label fs-4">Thời lượng (phút) <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="duration" name="duration" required
                                min="1" max="300" value="90">
                            <div class="invalid-feedback" id="error_duration"></div>
                        </div>
                    </div>

                    <!-- Ghi chú -->
                    <div class="mb-3">
                        <label for="note" class="form-label fs-4">Ghi chú</label>
                        <textarea class="form-control" id="note" name="note" rows="3" maxlength="500"></textarea>
                        <div class="invalid-feedback" id="error_note"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary fs-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitExamSchedule">
                        <span class="btn-text fs-4">Lưu</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
