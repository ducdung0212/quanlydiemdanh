<!-- Modal Xem chi tiết môn học -->
<div class="modal fade" id="viewSubjectModal" tabindex="-1" aria-labelledby="viewSubjectModalLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewSubjectModalLabel">Thông tin chi tiết môn học</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="subject-info-label">Mã Môn Học:</label>
                                <div class="subject-info-code">{{ $subject->subject_code }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="subject-info-label">Tên Môn Học:</label>
                                <div class="subject-info-name">{{ $subject->name }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="subject-info-label">Số Tín Chỉ:</label>
                                <div class="subject-info-value">{{ $subject->credit ?? 'Chưa cập nhật' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="subject-info-label">Ngày tạo:</label>
                                <div class="subject-info-value">{{ $subject->created_at ? $subject->created_at->format('d/m/Y H:i') : 'N/A' }}</div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="subject-info-label">Cập nhật lần cuối:</label>
                                <div class="subject-info-value">{{ $subject->updated_at ? $subject->updated_at->format('d/m/Y H:i') : 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<style>
.subject-info-label {
    font-weight: 600;
    color: #64748b;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
    display: block;
}

.subject-info-code,
.subject-info-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 0.5rem;
    border-left: 4px solid #3b82f6;
}

.subject-info-value {
    font-size: 1rem;
    color: #475569;
    padding: 0.5rem;
    background: #f8fafc;
    border-radius: 0.375rem;
}
</style>
