<!-- Modal Xem chi tiết sinh viên -->
<div class="modal fade" id="viewStudentModal" tabindex="-1" aria-labelledby="viewStudentModalLabel" aria-hidden="true"
    data-bs-backdrop="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewStudentModalLabel">Thông tin chi tiết sinh viên</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <!-- Ảnh sinh viên bên trái -->
                    <div class="col-md-4 text-center">
                        @php
                            $studentPhoto = $student->photos()->first();
                            $photoUrl = $studentPhoto ? $studentPhoto->image_url : asset('images/avatar/default-avatar.png');
                        @endphp
                        <img src="{{ $photoUrl }}" alt="Ảnh {{ $student->full_name }}"
                            class="img-fluid rounded border shadow-sm"
                            style="max-height: 300px; object-fit: cover; width: 100%;"
                            onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                        <div class="mt-2 small text-muted">
                            {{ $studentPhoto ? 'Đã đăng ký khuôn mặt' : 'Chưa đăng ký khuôn mặt' }}
                        </div>
                    </div>

                    <!-- Thông tin sinh viên bên phải -->
                    <div class="col-md-8">
                        <dl class="row mb-0">
                            <dt class="col-sm-4 text-muted">Mã Sinh Viên</dt>
                            <dd class="col-sm-8">{{ $student->student_code }}</dd>

                            <dt class="col-sm-4 text-muted">Họ và Tên</dt>
                            <dd class="col-sm-8">{{ $student->full_name }}</dd>

                            <dt class="col-sm-4 text-muted">Lớp</dt>
                            <dd class="col-sm-8">{{ $student->class_code ?: 'Chưa cập nhật' }}</dd>

                            <dt class="col-sm-4 text-muted">Số Điện Thoại</dt>
                            <dd class="col-sm-8">{{ $student->phone ?: 'Chưa cập nhật' }}</dd>

                            <dt class="col-sm-4 text-muted">Email</dt>
                            <dd class="col-sm-8">{{ $student->email ?: 'Chưa cập nhật' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
