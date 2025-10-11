<!-- Modal Import Excel -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);">
            <div class="modal-header" style="border-bottom: 1px solid #eee; padding: 20px 25px;">
                <h5 class="modal-title" id="importExcelModalLabel" style="font-size: 1.3rem; font-weight: 600;">Import danh sách sinh viên từ Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importExcelForm" enctype="multipart/form-data">
                <div class="modal-body" style="padding: 25px;">
                    <div class="mb-4">
                        <label for="excel_file" class="form-label" style="font-weight: 500;">Chọn file Excel</label>
                        <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls" required
                               style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                        <div class="form-text">Chỉ chấp nhận file Excel (.xlsx, .xls)</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-text">
                            <strong>Lưu ý:</strong> File Excel cần có các cột sau:
                            <ul>
                                <li>Mã Sinh Viên</li>
                                <li>Họ và Tên</li>
                                <li>Lớp</li>
                                <li>Số Điện Thoại</li>
                                <li>Email</li>
                                <li>Link Ảnh (tùy chọn)</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eee; padding: 20px 25px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            style="border-radius: 8px; padding: 10px 20px; border: none;">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="btnImportExcel"
                            style="border-radius: 8px; padding: 10px 20px; background-color: #2377FC; border: none;">
                        <span class="btn-text">Import</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

