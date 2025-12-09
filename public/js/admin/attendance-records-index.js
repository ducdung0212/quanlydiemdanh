document.addEventListener('DOMContentLoaded', function () {
    class AttendanceRecordManager extends BaseResourceManager {
        constructor() {
            super({
                apiBaseUrl: '/api/attendance-records',
                resourceName: 'điểm danh',
                resourceIdKey: 'id',
                bulkDeleteKey: 'record_ids',
                tableBodyId: 'attendance-records-table-body',
                checkboxClass: 'record-checkbox',
                addTriggerSelector: null, // Không có nút thêm
                importConfig: {
                    modalUrl: '/attendance-records/modals/import',
                    previewUrl: '/api/attendance-records/import/preview',
                    importUrl: '/api/attendance-records/import',
                    validateMapping: null // Không cần validation đặc biệt
                }
            });

            // Thêm filter riêng
            this.dom.sessionFilter = document.getElementById('sessionFilter');
        }
        
        /**
         * @override
         */
        renderTable() {
            const tb = this.dom.tableBody;
            const pd = this.state.paginationData;
            const q = this.state.currentQuery;
            if (!tb) return;

            if (!pd || !pd.data || pd.data.length === 0) {
                tb.innerHTML = `<tr><td colspan="6" class="text-center">${q ? 'Không tìm thấy bản ghi' : 'Không có dữ liệu'}</td></tr>`;
                return;
            }

            const { data: records, from } = pd;
            const rowsHtml = records.map((record, index) => {
                const isChecked = this.state.selectedItems.has(record.id.toString()) ? 'checked' : '';
                return `
                    <tr>
                        <td style="text-align: center;">
                            <input type="checkbox" class="record-checkbox" value="${escapeHtml(record.id)}" ${isChecked} style="cursor: pointer;" data-action="toggle-select">
                        </td>
                        <td style="text-align: center;">${from + index}</td>
                        <td style="text-align: left;">${escapeHtml(record.exam_schedule_id || '')}</td>
                        <td style="text-align: left;">${escapeHtml(record.student_code || '')}</td>
                        <td style="text-align: left;">${escapeHtml(record.student_name || '')}</td>
                        <td style="text-align: center;">
                            <div class="list-icon-function">
                                <a href="#" data-action="delete-record" data-id="${escapeHtml(record.id)}" title="Xóa">
                                    <div class="item text-danger delete"><i class="icon-trash-2"></i></div>
                                </a>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

            tb.innerHTML = rowsHtml;
        }

        /**
         * @override
         */
        getFetchUrl(page, query) {
            const session = this.dom.sessionFilter ? this.dom.sessionFilter.value : '';
            return `${this.options.apiBaseUrl}?page=${page}&q=${encodeURIComponent(query)}&session=${encodeURIComponent(session)}`;
        }

        /**
         * @override
         */
        updateURL(page, query) {
            const session = this.dom.sessionFilter ? this.dom.sessionFilter.value : '';
            const url = new URL(window.location);
            url.searchParams.set('page', page);
            url.searchParams.set('q', query || '');
            url.searchParams.set('session', session || '');
            window.history.replaceState({}, '', url);
        }

        /**
         * @override
         */
        getURLParams() {
            const urlParams = new URLSearchParams(window.location.search);
            return {
                page: parseInt(urlParams.get('page')) || 1,
                query: urlParams.get('q') || '',
                session: urlParams.get('session') || ''
            };
        }
        
        /**
         * @override
         */
        setupEventListeners() {
            super.setupEventListeners(); // Gắn sự kiện chung

            // Gắn sự kiện riêng cho sessionFilter
            if (this.dom.sessionFilter) {
                this.dom.sessionFilter.addEventListener('change', () => {
                    this.fetchData(1, this.state.currentQuery);
                });
            }

            // Gắn sự kiện riêng cho table
            this.dom.tableBody.addEventListener('click', async (event) => {
                const target = event.target.closest('[data-action]');
                if (!target) return;
                
                const action = target.dataset.action;
                const id = target.dataset.id;

                switch (action) {
                    case 'delete-record':
                        event.preventDefault();
                        if (id) this.deleteItem(id);
                        break;
                    case 'toggle-select':
                        const checkbox = event.target.closest('.record-checkbox');
                        if (checkbox) this.toggleSelection(checkbox.value, checkbox.checked);
                        break;
                }
            });
            
            document.addEventListener('resourceUpdated', (e) => {
                const isEdit = e.detail?.isEdit || false;
                this.fetchData(isEdit ? this.state.currentPage : 1, this.state.currentQuery);
            });
        }
        
        /**
         * @override
         */
        init() {
            this.setupEventListeners();
            const { page, query, session } = this.getURLParams();
            if (this.dom.searchInput) this.dom.searchInput.value = query;
            if (this.dom.sessionFilter) this.dom.sessionFilter.value = session;
            this.fetchData(page, query);
        }
    }

    const manager = new AttendanceRecordManager();
    manager.init();
});