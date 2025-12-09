<div class="modal fade" id="manageStudentsModal" tabindex="-1" aria-labelledby="manageStudentsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down" style="max-width: 1400px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageStudentsModalLabel">Quản lý Sinh viên tham gia ca thi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="input-group input-group-lg">
                        <input type="text" class="form-control" id="searchStudent"
                            placeholder="Nhập mã sinh viên...">
                        <button class="btn btn-primary" type="button" id="btnAddStudent">
                            <i class="icon-plus"></i> Thêm
                        </button>
                    </div>
                </div>
                <div style="overflow-x: auto;">
                    <table class="table table-striped table-bordered " style="table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th class="align-middle" style="width: 14%;">STT</th>
                                <th class="align-middle" style="width: 20%;">Mã SV</th>
                                <th class="align-middle" style="width: 35%;">Họ tên</th>
                                <th class="align-middle" style="width: 20%;">Lớp</th>
                                <th class="align-middle" style="width: 16%x;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="students-list-body">
                            <tr>
                                <td colspan="5" class="text-center">Đang tải...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="students-pagination"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
