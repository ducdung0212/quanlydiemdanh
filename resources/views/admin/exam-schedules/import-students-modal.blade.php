<!-- Modal Import Students từ Excel -->
<div class="modal fade" id="importStudentsExcelModal" tabindex="-1" aria-labelledby="importStudentsExcelModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content"
            style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);">
            <div class="modal-header" style="border-bottom: 1px solid #eee; padding: 20px 25px;">
                <h5 class="modal-title" id="importStudentsExcelModalLabel" style="font-size: 1.6rem; font-weight: 600;">
                    Import danh sách sinh viên từ Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importStudentsExcelForm" enctype="multipart/form-data">
                <div class="modal-body" style="padding: 25px;">
                    <div class="mb-4">
                        <label for="students_excel_file" class="form-label"
                            style="font-weight: 500; font-size: 1.3rem;">Chọn file Excel
                            <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                            <div class="alert alert-info mt-3" style="font-size: 1.3rem;">
                                <i class="icon-info"></i> <strong>Lưu ý:</strong> File Excel cần có ít nhất cột
                                <strong>Mã sinh viên</strong>. Các cột khác (Họ tên, Lớp) là tùy chọn.
                            </div>
                            <a class="btn btn-outline-primary btn-sm"
                                href="{{ asset('excel_template/Import_Student_Template.xlsx') }}" download
                                style="border-radius: 8px; padding: 8px 14px; font-size: 1.3rem;">
                                Tải file mẫu
                            </a>
                        </div>
                        <input type="file" class="form-control form-control-lg" id="students_excel_file"
                            name="excel_file" accept=".xlsx,.xls" required
                            style="border-radius: 8px; padding: 14px; border: 1px solid #ddd; font-size: 1.3rem;">
                        <div class="form-text" style="font-size: 1.3rem;">Chỉ chấp nhận file Excel (.xlsx, .xls)</div>
                    </div>
                    <input type="hidden" name="token" id="import_students_token">
                    <input type="hidden" name="heading_row" id="import_students_heading_row">
                    <div class="mb-4 d-none" id="studentsHeadingsPreview">
                        <div class="form-text mb-2">Các cột tìm thấy trong file:</div>
                        <div id="studentsHeadingsList" class="d-flex flex-wrap gap-2"></div>
                    </div>
                    <div class="mb-3">
                        <div class="form-text" style="font-size: 1rem;">
                            <strong style="font-size: 1.3rem;">Quy trình:</strong>
                            <br>
                            <br>
                            <ol class="mb-2" style="font-size: 1.3rem;">
                                <li>Bước 1: Chọn file và bấm <em>Tiếp tục</em> để tải tiêu đề cột.</li>
                                <br>
                                <li>Bước 2: Ghép từng cột trong file với trường dữ liệu bên dưới, sau đó bấm
                                    <em>Import</em>.
                                </li>
                            </ol>
                            
                        </div>
                    </div>
                    <div class="d-none" id="studentsMappingSection">
                        <div class="alert alert-secondary" role="alert" style="font-size: 1.3rem;">
                            Chọn cột tương ứng cho từng trường. <strong>*</strong> là bắt buộc.
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" style="font-size: 1.3rem; font-weight: 500;">Mã sinh viên
                                    <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg column-mapping-students"
                                    data-field="student_code" data-required="true" style="font-size: 1.3rem;">
                                    <option value="">-- Chọn cột --</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label" style="font-size: 1.3rem; font-weight: 500;">Họ tên</label>
                                <select class="form-select form-select-lg column-mapping-students"
                                    data-field="full_name" data-required="false" style="font-size: 1.3rem;">
                                    <option value="">-- Chọn cột --</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label" style="font-size: 1.3rem; font-weight: 500;">Lớp</label>
                                <select class="form-select form-select-lg column-mapping-students"
                                    data-field="class_code" data-required="false" style="font-size: 1.3rem;">
                                    <option value="">-- Chọn cột --</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eee; padding: 20px 25px;">
                    <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal"
                        style="border-radius: 8px; padding: 12px 24px; border: none; font-size: 1.3rem;">Hủy</button>
                    <button type="submit" class="btn btn-primary btn-lg" id="btnImportStudentsSubmit"
                        style="border-radius: 8px; padding: 12px 24px; background-color: #2377FC; border: none; font-size: 1.3rem;">
                        <span class="btn-text" data-text-preview="Tiếp tục" data-text-import="Import">Tiếp tục</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
