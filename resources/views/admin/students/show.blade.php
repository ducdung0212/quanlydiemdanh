<!-- Modal Xem chi tiết sinh viên -->
<div class="modal fade" id="viewStudentModal" tabindex="-1" aria-labelledby="viewStudentModalLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);">
            <div class="modal-header" style="border-bottom: 1px solid #eee; padding: 20px 25px;">
                <h5 class="modal-title" id="viewStudentModalLabel" style="font-size: 1.3rem; font-weight: 600;">Thông tin chi tiết sinh viên</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 25px;">
                <div class="row mb-4">
                    <div class="col-md-3 text-center">
                        <div id="studentAvatarContainer">
                            @if($student->photo_url)
                                <img id="studentAvatar" src="{{ $student->photo_url }}" alt="Ảnh đại diện" class="img-fluid rounded" style="max-height: 200px;">
                            @else
                                <div id="noAvatar" class="text-muted mt-2">Không có ảnh</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Mã Sinh Viên:</strong>
                                <div class="text-primary">{{ $student->student_code }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Họ và Tên:</strong>
                                <div>{{ $student->full_name }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Lớp:</strong>
                                <div>{{ $student->class_code }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Số Điện Thoại:</strong>
                                <div>{{ $student->phone ?: 'Chưa cập nhật' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Email:</strong>
                                <div>{{ $student->email }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #eee; padding: 20px 25px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        style="border-radius: 8px; padding: 10px 20px; border: none;">Đóng</button>
            </div>
        </div>
    </div>
</div>
