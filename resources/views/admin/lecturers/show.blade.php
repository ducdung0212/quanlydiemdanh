<!-- Modal Xem chi tiết giảng viên -->
<div class="modal fade" id="viewLecturerModal" tabindex="-1" aria-labelledby="viewLecturerModalLabel" aria-hidden="true" data-bs-backdrop="false">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="viewLecturerModalLabel">Thông tin chi tiết giảng viên</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row g-3">
					<div class="col-md-8">
						{{-- Tăng kích thước chữ để khớp với modal sinh viên: dd dùng fs-4, tên dùng fs-3 fw-bold --}}
						<dl class="row mb-0 fs-4">
							<dt class="col-sm-4 text-muted">Mã Giảng Viên</dt>
							<dd class="col-sm-8">{{ $lecturer->lecturer_code }}</dd>

							<dt class="col-sm-4 text-muted">Họ và Tên</dt>
							<dd class="col-sm-8">{{ $lecturer->full_name }}</dd>

							<dt class="col-sm-4 text-muted">Khoa</dt>
							<dd class="col-sm-8">{{ $lecturer->faculty_code ?: 'Chưa cập nhật' }}</dd>

							<dt class="col-sm-4 text-muted">Người dùng liên kết</dt>
							<dd class="col-sm-8">{{ $lecturer->user_id ?: 'Chưa liên kết' }}</dd>

							<dt class="col-sm-4 text-muted">Email</dt>
							<dd class="col-sm-8">{{ $lecturer->email ?: 'Chưa cập nhật' }}</dd>

							<dt class="col-sm-4 text-muted">Số Điện Thoại</dt>
							<dd class="col-sm-8">{{ $lecturer->phone ?: 'Chưa cập nhật' }}</dd>
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
