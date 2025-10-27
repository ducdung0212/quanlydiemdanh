<!-- Modal Sửa môn học -->
<div class="modal fade" id="editSubjectModal" tabindex="-1" aria-labelledby="editSubjectModalLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSubjectModalLabel">Sửa thông tin môn học</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSubjectForm">
                <div class="modal-body">
                    <input type="hidden" id="editSubjectId" value="{{ $subject->subject_code }}">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="editSubjectCode" class="form-label">Mã Môn Học <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editSubjectCode" name="subject_code" required value="{{ $subject->subject_code }}">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="editName" class="form-label">Tên Môn Học <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editName" name="name" required value="{{ $subject->name }}">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="editCredit" class="form-label">Số Tín Chỉ</label>
                            <input type="number" class="form-control" id="editCredit" name="credit" min="1" max="10" value="{{ $subject->credit }}">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="btnEditSubject">
                        <span class="btn-text">Lưu thay đổi</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
