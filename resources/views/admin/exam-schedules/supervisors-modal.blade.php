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
                    <div class="input-group input-group-lg">
                        <input type="text" class="form-control" id="searchSupervisor"
                            placeholder="Nhập mã giảng viên để thêm vào ca thi...">
                        <button class="btn btn-primary" type="button" id="btnAddSupervisor">
                            <i class="icon-plus"></i> Thêm
                        </button>
                    </div>
                </div>
                <div style="overflow-x: auto;">
                    <table class="table table-striped table-bordered" style="table-layout: fixed; width: 100%;">
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
                <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
