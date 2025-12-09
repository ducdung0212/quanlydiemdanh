<!-- Modal Export By Date -->
<div class="modal fade" id="exportByDateModal" tabindex="-1" aria-labelledby="exportByDateModalLabel" aria-hidden="true"
    data-bs-backdrop="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content"
            style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);">
            <div class="modal-header" style="border-bottom: 1px solid #eee; padding: 20px 25px;">
                <h5 class="modal-title" id="exportByDateModalLabel" style="font-size: 1.3rem; font-weight: 600;">Export
                    ca thi theo ngày</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="exportByDateForm">
                <div class="modal-body" style="padding: 25px;">
                    <div class="mb-4">
                        <label for="export_date" class="form-label" style="font-weight: 500;">Chọn ngày thi <span
                                class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="export_date" name="export_date" required
                            style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                        <div class="form-text">Chọn ngày để export tất cả ca thi trong ngày đó</div>
                    </div>
                </div>
               <div class="modal-footer" style="border-top: 1px solid #eee; padding: 20px 25px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        style="border-radius: 10px; padding: 12px 24px; border: none; font-size: 15px; min-width: 120px;">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="btnExportByDateSubmit"
                        style="border-radius: 10px; padding: 12px 24px; background-color: #2377FC; border: none; font-size: 15px; min-width: 120px;">
                        <span class="btn-text">Export</span>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
