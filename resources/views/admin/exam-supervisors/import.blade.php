<!-- Modal Import Excel -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);">
            <div class="modal-header" style="border-bottom: 1px solid #eee; padding: 20px 25px;">
                <h5 class="modal-title" id="importExcelModalLabel" style="font-size: 1.3rem; font-weight: 600;">Import danh sách giám thị từ Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importExcelForm" enctype="multipart/form-data">
                <div class="modal-body" style="padding: 25px;">
                    <div class="mb-4">
                        <label for="excel_file" class="form-label" style="font-weight: 500;">Chọn file Excel <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls" required
                               style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                        <div class="form-text">Chỉ chấp nhận file Excel (.xlsx, .xls)</div>
                    </div>
                    <input type="hidden" name="token" id="import_token">
                    <input type="hidden" name="heading_row" id="import_heading_row">
                    <div class="mb-4 d-none" id="headingsPreview">
                        <div class="form-text mb-2">Các cột tìm thấy trong file:</div>
                        <div id="headingsList" class="d-flex flex-wrap gap-2"></div>
                    </div>
                    <div class="mb-3">
                        <div class="form-text">
                            <strong>Quy trình:</strong>
                            <br>
                            <br>
                            <ol class="mb-2">
                                <li>Bước 1: Chọn file và bấm <em>Tiếp tục</em> để tải tiêu đề cột.</li>
                                <br>
                                <li>Bước 2: Ghép từng cột trong file với trường dữ liệu bên dưới, sau đó bấm <em>Import</em>.</li>
                            </ol>
                        </div>
                    </div>
                    <div class="d-none" id="mappingSection">
                        <div class="alert alert-secondary" role="alert">
                            Chọn cột tương ứng cho từng trường. <strong>*</strong> là bắt buộc.
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Mã Giảng Viên <span class="text-danger">*</span></label>
                                <select class="form-select column-mapping" data-field="lecturer_code" data-required="true">
                                    <option value="">-- Chọn cột --</option>
                                </select>
                            </div>
                             <div class="col-12">
                                <label class="form-label">Mã môn học <span class="text-danger">*</span></label>
                                <select class="form-select column-mapping" data-field="subject_code" data-required="true">
                                    <option value="">-- Chọn cột --</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Ngày thi <span class="text-danger">*</span></label>
                                <select class="form-select column-mapping" data-field="exam_date" data-required="true">
                                    <option value="">-- Chọn cột --</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Giờ thi <span class="text-danger">*</span></label>
                                <select class="form-select column-mapping" data-field="exam_time" data-required="true">
                                    <option value="">-- Chọn cột --</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Phòng <span class="text-danger">*</span></label>
                                <select class="form-select column-mapping" data-field="room" data-required="true">
                                    <option value="">-- Chọn cột --</option>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eee; padding: 20px 25px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            style="border-radius: 8px; padding: 10px 20px; border: none;">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="btnImportExcel"
                            style="border-radius: 8px; padding: 10px 20px; background-color: #2377FC; border: none;">
                        <span class="btn-text" data-text-preview="Tiếp tục" data-text-import="Import">Tiếp tục</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>