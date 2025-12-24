<div class="modal fade" id="manageSupervisorsModal" tabindex="-1" aria-labelledby="manageSupervisorsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down" style="max-width: 1400px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageSupervisorsModalLabel">Quản lý Giám thị ca thi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="alert alert-info mt-3 fs-4">
                        <i class="icon-info"></i> <strong>Lưu ý:</strong>Gõ để tra cứu theo mã giảng viên / họ tên /
                        email, sau đó bấm chọn để phân công giảng viên.
                    </div>
                    <div class="input-group input-group-lg">
                        <input type="text" class="form-control" id="searchSupervisor"
                            placeholder="Mã giảng viên, họ tên, email...">
                    </div>
                    <div id="lecturerLookupSection" class="mt-2 d-none">
                        <div id="lecturerLookupStatus" class="small text-muted mb-1 fs-4"></div>
                        <div id="lecturerLookupList" class="list-group fs-4"></div>
                    </div>
                </div>
                <div style="overflow-x: auto;">
                    <table class="table table-striped table-bordered align-middle"
                        style="table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th class="align-middle text-center" style="width: 80px; ">STT</th>
                                <th class="align-middle text-center" style="width: 150px; ">Mã GV</th>
                                <th class="align-middle" style="white-space: nowrap;">Họ tên</th>
                                <th class="align-middle text-center" style="width: 120px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="supervisors-list-body">
                            <tr>
                                <td colspan="4" class="text-center">Đang tải...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-lg fs-4" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
