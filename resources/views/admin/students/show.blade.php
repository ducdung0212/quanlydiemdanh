php<!-- Modal Xem chi tiết sinh viên -->
<div class="modal fade" id="viewStudentModal" tabindex="-1" aria-labelledby="viewStudentModalLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewStudentModalLabel">Thông tin chi tiết sinh viên</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-3 mb-md-0 student-avatar-col">
                        <div id="studentAvatarContainer" class="student-avatar-container">
                            @if($student->photo_url)
                                <img id="studentAvatar" src="{{ $student->photo_url }}" alt="Ảnh đại diện" class="student-avatar-img">
                            @else
                                <div id="noAvatar" class="student-no-avatar">
                                    <i class="icon-image"></i>
                                    <span>Không có ảnh</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-md-12 mb-3 student-info-main">
                                <label class="student-info-label">Mã Sinh Viên:</label>
                                <div class="student-info-code">{{ $student->student_code }}</div>
                            </div>
                            <div class="col-md-12 mb-3 student-info-main">
                                <label class="student-info-label">Họ và Tên:</label>
                                <div class="student-info-name">{{ $student->full_name }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="student-info-label">Lớp:</label>
                                <div class="student-info-value">{{ $student->class_code }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="student-info-label">Số Điện Thoại:</label>
                                <div class="student-info-value">{{ $student->phone ?: 'Chưa cập nhật' }}</div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="student-info-label">Email:</label>
                                <div class="student-info-value student-info-email">{{ $student->email ?: 'Chưa cập nhật' }}</div>
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
