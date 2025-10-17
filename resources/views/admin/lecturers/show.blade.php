<!-- Modal Xem chi tiết giảng viên -->
<div class="modal fade" id="viewLecturerModal" tabindex="-1" aria-labelledby="viewLecturerModalLabel" aria-hidden="true" data-bs-backdrop="false">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="viewLecturerModalLabel">Thông tin chi tiết giảng viên</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-4 text-center mb-3 mb-md-0 student-avatar-col">
						<div class="student-avatar-container d-flex flex-column justify-content-center align-items-center">
							<div class="student-no-avatar">
								<i class="icon-user"></i>
								<span>{{ $lecturer->full_name }}</span>
							</div>
							<small class="text-muted mt-2">{{ $lecturer->lecturer_code }}</small>
						</div>
					</div>
					<div class="col-md-8">
						<div class="row g-3">
							<div class="col-md-12 mb-3 student-info-main">
								<label class="student-info-label">Mã Giảng Viên:</label>
								<div class="student-info-code">{{ $lecturer->lecturer_code }}</div>
							</div>
							<div class="col-md-12 mb-3 student-info-main">
								<label class="student-info-label">Họ và Tên:</label>
								<div class="student-info-name">{{ $lecturer->full_name }}</div>
							</div>
							<div class="col-md-6 mb-3">
								<label class="student-info-label">Khoa:</label>
								<div class="student-info-value">{{ $lecturer->faculty_code ?: 'Chưa cập nhật' }}</div>
							</div>
							<div class="col-md-6 mb-3">
								<label class="student-info-label">Người dùng liên kết:</label>
								<div class="student-info-value">{{ $lecturer->user_id ?: 'Chưa liên kết' }}</div>
							</div>
							<div class="col-md-6 mb-3">
								<label class="student-info-label">Email:</label>
								<div class="student-info-value student-info-email">{{ $lecturer->email ?: 'Chưa cập nhật' }}</div>
							</div>
							<div class="col-md-6 mb-3">
								<label class="student-info-label">Số Điện Thoại:</label>
								<div class="student-info-value">{{ $lecturer->phone ?: 'Chưa cập nhật' }}</div>
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
