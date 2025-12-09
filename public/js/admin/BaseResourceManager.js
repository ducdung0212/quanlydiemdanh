/**
 * LỚP CHA (BASE CLASS) QUẢN LÝ TÀI NGUYÊN
 *
 * Lớp này chứa tất cả logic chung cho việc
 * - Tìm kiếm, Phân trang, Tải dữ liệu (Fetch)
 * - Quản lý State (currentPage, isLoading, selectedItems)
 * - Render Phân trang
 * - Xử lý Xóa (đơn) và Xóa hàng loạt
 * - Xử lý Form (handleFormSubmit)
 * - Xử lý Import Excel (2 bước)
 *
 * Các lớp con (StudentManager, SubjectManager, v.v.) sẽ kế thừa lớp này.
 */
class BaseResourceManager {
    constructor(options) {
        // --- Cấu hình từ lớp con ---
        this.options = {
            apiBaseUrl: options.apiBaseUrl, // Bắt buộc
            resourceName: options.resourceName || 'bản ghi', // Tên (vd: 'sinh viên')
            resourceIdKey: options.resourceIdKey || 'id', // Khóa chính (vd: 'student_code' hoặc 'id')
            bulkDeleteKey: options.bulkDeleteKey, // Key cho API xóa (vd: 'student_codes')
            importConfig: options.importConfig || null, // { previewUrl, importUrl, requiredField }
            checkboxClass: options.checkboxClass || 'resource-checkbox' // class của checkbox
        };

        this.DEBOUNCE_DELAY = 300;

        // --- DOM Elements (Chung) ---
        this.dom = {
            searchInput: document.getElementById('searchInput'),
            searchForm: document.getElementById('searchForm'),
            tableBody: document.getElementById(options.tableBodyId),
            paginationContainer: document.getElementById('pagination-container'),
            paginationInfo: {
                start: document.getElementById('pagination-start'),
                end: document.getElementById('pagination-end'),
                total: document.getElementById('pagination-total')
            },
            btnBulkDelete: document.getElementById('btnBulkDelete'),
            selectedCountSpan: document.getElementById('selectedCount'),
            selectAllCheckbox: document.getElementById('selectAll'),
            importExcelBtn: document.getElementById('importExcelBtn'),
            // Nút "Thêm mới" (lớp con sẽ tự gán sự kiện)
            // Hỗ trợ 2 cách cấu hình từ lớp con:
            // - truyền `modalPrefix` (ví dụ 'Student') -> tìm selector [data-bs-target*="#addStudentModal"]
            // - hoặc truyền `addTriggerSelector` trực tiếp (ví dụ '[data-bs-target="#addSubjectModal"]')
            addTrigger: options.addTriggerSelector ? document.querySelector(options.addTriggerSelector) : document.querySelector(`[data-bs-target*="#add${options.modalPrefix || ''}Modal"]`)
        };

        // --- State (Chung) ---
        this.state = {
            currentPage: 1,
            currentQuery: '',
            paginationData: null,
            isLoading: false,
            selectedItems: new Set() // Dùng tên chung
        };

        // --- Modals (Chung) ---
        this.modals = {
            add: null,
            edit: null,
            view: null,
            import: null
        };
        
        // --- Bind methods ---
        this.render = this.render.bind(this);
        this.fetchData = this.fetchData.bind(this);
    }

    // ------------------------------------------------------------------
    // CÁC PHƯƠNG THỨC TRỪU TƯỢNG (Lớp con PHẢI định nghĩa)
    // ------------------------------------------------------------------

    /**
     * @abstract
     * Lớp con phải định nghĩa hàm này để render HTML cho table body.
     */
    renderTable() {
        throw new Error('Phương thức renderTable() chưa được định nghĩa bởi lớp con.');
    }

    /**
     * @abstract
     * Lớp con phải định nghĩa hàm này để trả về URL fetch (có thể thêm filter)
     */
    getFetchUrl(page, query) {
        return `${this.options.apiBaseUrl}?page=${page}&q=${encodeURIComponent(query)}`;
    }

    // ------------------------------------------------------------------
    // CÁC PHƯƠNG THỨC API (Chung)
    // ------------------------------------------------------------------

    async apiFetch(page = 1, query = '') {
        const url = this.getFetchUrl(page, query);
        return await apiFetch(url);
    }

    async apiDelete(id) {
        return await apiFetch(`${this.options.apiBaseUrl}/${encodeURIComponent(id)}`, { method: 'DELETE' });
    }

    async apiBulkDelete(ids) {
        if (!this.options.bulkDeleteKey) {
            throw new Error('bulkDeleteKey chưa được cấu hình.');
        }
        const body = {};
        body[this.options.bulkDeleteKey] = ids;
        
        return await apiFetch(`${this.options.apiBaseUrl}/bulk-delete`, {
            method: 'POST',
            body: JSON.stringify(body)
        });
    }

    // ------------------------------------------------------------------
    // LOGIC FETCH VÀ RENDER (Chung)
    // ------------------------------------------------------------------

    async fetchData(page = 1, query = '') {
        if (this.state.isLoading) return;
        this.state.isLoading = true;
        this.dom.tableBody.innerHTML = `<tr><td colspan="10" class="text-center">Đang tải...</td></tr>`;

        try {
            const result = await this.apiFetch(page, query);
            if (result.success && result.data) {
                this.state.paginationData = result.data;
                this.state.currentPage = this.state.paginationData.current_page;
                this.state.currentQuery = query;
                this.updateURL(this.state.currentPage, this.state.currentQuery);
                this.render();
            } else {
                throw new Error(result.message || 'Invalid API response format');
            }
        } catch (err) {
            console.error('Failed to fetch data:', err);
            this.dom.tableBody.innerHTML = `<tr><td colspan="10" class="text-center text-danger">Lỗi khi tải dữ liệu.</td></tr>`;
            this.state.paginationData = null;
            this.render();
        } finally {
            this.state.isLoading = false;
        }
    }

    render() {
        this.renderTable(); // Gọi hàm của lớp con
        this.renderPagination();
        this.updateBulkDeleteButton();
        this.updateSelectAllCheckbox();
    }

    renderPagination() {
        const pd = this.state.paginationData;
        if (!this.dom.paginationContainer) return;
        
        if (!pd) {
            this.dom.paginationContainer.innerHTML = '';
            updatePaginationInfo(this.dom.paginationInfo, pd);
            return;
        }
        this.dom.paginationContainer.innerHTML = renderPaginationHTML(pd);
        updatePaginationInfo(this.dom.paginationInfo, pd);
    }

    // ------------------------------------------------------------------
    // LOGIC XÓA VÀ CHỌN (Chung)
    // ------------------------------------------------------------------

    updateBulkDeleteButton() {
        const count = this.state.selectedItems.size;
        if (this.dom.selectedCountSpan) this.dom.selectedCountSpan.textContent = count;
        if (this.dom.btnBulkDelete) {
            this.dom.btnBulkDelete.classList.toggle('d-none', count === 0);
        }
    }

    toggleSelection(id, isSelected) {
        const idStr = id.toString();
        if (isSelected) this.state.selectedItems.add(idStr);
        else this.state.selectedItems.delete(idStr);
        this.updateBulkDeleteButton();
        this.updateSelectAllCheckbox();
    }

    updateSelectAllCheckbox() {
        if (!this.dom.selectAllCheckbox) return;
        
        const checkboxes = this.dom.tableBody.querySelectorAll(`.${this.options.checkboxClass}`);
        if (checkboxes.length === 0) {
            this.dom.selectAllCheckbox.checked = false;
            this.dom.selectAllCheckbox.indeterminate = false;
            return;
        }
        const allSelected = Array.from(checkboxes).every(cb => this.state.selectedItems.has(cb.value));
        const someSelected = Array.from(checkboxes).some(cb => this.state.selectedItems.has(cb.value));
        this.dom.selectAllCheckbox.checked = allSelected;
        this.dom.selectAllCheckbox.indeterminate = !allSelected && someSelected;
    }

    async deleteItem(id) {
        if (!confirm(`Bạn có chắc chắn muốn xóa ${this.options.resourceName} này? (ID: ${id})`)) return;
        try {
            const result = await this.apiDelete(id);
            if (result.success) {
                showToast('Thành công', result.message, 'success');
                this.state.selectedItems.delete(id.toString());
                const remainingRows = this.dom.tableBody.querySelectorAll('tr').length;
                if (remainingRows === 1 && this.state.currentPage > 1) {
                    await this.fetchData(this.state.currentPage - 1, this.state.currentQuery);
                } else {
                    await this.fetchData(this.state.currentPage, this.state.currentQuery);
                }
            } else {
                showToast('Lỗi', result.message, 'danger');
            }
        } catch (err) {
            showToast('Lỗi', err.message || `Không thể xóa ${this.options.resourceName}`, 'danger');
        }
    }

    async bulkDeleteItems() {
        const count = this.state.selectedItems.size;
        if (count === 0) return;
        if (!confirm(`Bạn có chắc chắn muốn xóa ${count} ${this.options.resourceName} đã chọn?`)) return;

        const ids = Array.from(this.state.selectedItems);
        try {
            const result = await this.apiBulkDelete(ids);
            if (result.success) {
                showToast('Thành công', result.message, 'success');
                this.state.selectedItems.clear();
                await this.fetchData(1, '');
            } else {
                showToast('Lỗi', result.message, 'danger');
            }
        } catch (err) {
            showToast('Lỗi', err.message || 'Không thể xóa hàng loạt', 'danger');
        }
    }

    // ------------------------------------------------------------------
    // LOGIC FORM VÀ IMPORT (Chung)
    // ------------------------------------------------------------------

    async handleFormSubmit(form, submitFn, modalInstance = null, successEventName = null, successEventDetail = null) {
        const submitButton = form.querySelector('button[type="submit"]');
        try {
            toggleButtonLoading(submitButton, true);
            clearValidationErrors(form);
            const result = await submitFn();
            
            if (!result || !result.success) {
                showToast('Lỗi', result?.message || 'Tác vụ thất bại', 'danger');
                return result;
            }
            if (modalInstance && typeof modalInstance.hide === 'function') modalInstance.hide();
            if (successEventName) document.dispatchEvent(new CustomEvent(successEventName, { detail: successEventDetail }));
            return result;
        } catch (err) {
            if (err.statusCode === 422 && err.errors) {
                displayValidationErrors(form, err.errors);
            } else {
                showToast('Lỗi', err.message || 'Có lỗi xảy ra', 'danger');
            }
            throw err;
        } finally {
            toggleButtonLoading(submitButton, false);
        }
    }

    initializeImportModal(modalInstance) {
        const config = this.options.importConfig;
        if (!config || !modalInstance) return;

        const modalElement = modalInstance._element || modalInstance;
        const form = modalElement.querySelector('form');
        if (!form || form.dataset.initialized === 'true') return;

        const fileInput = form.querySelector('#excel_file');
        const tokenInput = form.querySelector('#import_token');
        const headingRowInput = form.querySelector('#import_heading_row');
        const mappingSection = form.querySelector('#mappingSection');
        const headingsPreview = form.querySelector('#headingsPreview');
        const headingsList = form.querySelector('#headingsList');
        const submitButton = form.querySelector('button[type="submit"]');
        const buttonText = submitButton ? submitButton.querySelector('.btn-text') : null;
        const mappingSelects = Array.from(form.querySelectorAll('.column-mapping'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Debug logging
        console.log('Import Modal Initialized:', {
            config,
            hasForm: !!form,
            hasFileInput: !!fileInput,
            hasMappingSection: !!mappingSection,
            mappingSelectsCount: mappingSelects.length
        });

        mappingSelects.forEach((select) => {
            select.dataset.defaultOptions = select.innerHTML;
            select.disabled = true;
            select.required = false;
        });

        const resetMappingUI = () => {
            if (mappingSection) mappingSection.classList.add('d-none');
            if (headingsPreview) headingsPreview.classList.add('d-none');
            if (headingsList) headingsList.innerHTML = '';
            
            mappingSelects.forEach((select) => {
                select.innerHTML = select.dataset.defaultOptions || '<option value="">-- Chọn cột --</option>';
                select.disabled = true;
                select.required = false;
            });
            
            tokenInput.value = '';
            headingRowInput.value = '';
            form.dataset.step = '';
            
            if (buttonText) {
                buttonText.textContent = buttonText.dataset.textPreview || 'Tiếp tục';
            }
        };

        const populateHeadings = (headings) => {
            if (!headings || headings.length === 0) {
                if (headingsPreview) headingsPreview.classList.add('d-none');
                return;
            }
            
            // Hiển thị preview các cột
            if (headingsPreview) headingsPreview.classList.remove('d-none');
            if (headingsList) {
                headingsList.innerHTML = headings.map(h => 
                    `<span class="badge bg-secondary" style="font-size: 0.9rem; padding: 6px 12px;">${escapeHtml(h)}</span>`
                ).join('');
            }
            
            // Hiển thị mapping section
            if (mappingSection) mappingSection.classList.remove('d-none');
            
            // Populate options cho các select
            mappingSelects.forEach((select) => {
                const defaultOpt = select.querySelector('option[value=""]');
                const defaultText = defaultOpt ? defaultOpt.textContent : '-- Chọn cột --';
                
                select.innerHTML = `<option value="">${defaultText}</option>` + 
                    headings.map(h => `<option value="${escapeHtml(h)}">${escapeHtml(h)}</option>`).join('');
                
                select.disabled = false;
                
                // Set required nếu data-required="true"
                if (select.dataset.required === 'true') {
                    select.required = true;
                }
            });
        };

        const handlePreview = async () => {
            if (!fileInput.files.length) throw new Error('Vui lòng chọn file Excel.');
            
            console.log('Starting preview...', {
                fileName: fileInput.files[0].name,
                fileSize: fileInput.files[0].size,
                previewUrl: config.previewUrl
            });
            
            const previewData = new FormData();
            previewData.append('excel_file', fileInput.files[0]);
            
            const response = await fetch(config.previewUrl, {
                method: 'POST', body: previewData, headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const result = await response.json();
            
            console.log('Preview response:', result);
            
            if (!response.ok || !result.success) throw new Error(result.message || 'Không thể đọc tiêu đề cột');
            
            tokenInput.value = result.token;
            headingRowInput.value = result.heading_row || '';
            populateHeadings(result.headings || []);
            form.dataset.step = 'mapping';
            if (buttonText) buttonText.textContent = buttonText.dataset.textImport || 'Import';
            showToast('Thông báo', 'Vui lòng map các cột.', 'info');
        };

        const handleImport = async () => {
            if (!tokenInput.value) throw new Error('Vui lòng tải lại file.');
            const mapping = {};
            mappingSelects.forEach((select) => { mapping[select.dataset.field] = select.value; });

            // Nếu lớp con cung cấp hàm validateMapping thì gọi hàm đó (cho phép kiểm tra nhiều trường),
            // ngược lại fallback về requiredField đơn giản.
            if (typeof config.validateMapping === 'function') {
                try {
                    config.validateMapping(mapping);
                } catch (err) {
                    throw new Error(err && err.message ? err.message : 'Mapping không hợp lệ.');
                }
            } else if (config.requiredField && !mapping[config.requiredField]) {
                throw new Error(`Vui lòng chọn cột cho ${config.requiredField}.`);
            }
            
            const response = await fetch(config.importUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({
                    token: tokenInput.value,
                    heading_row: headingRowInput.value ? Number(headingRowInput.value) : undefined,
                    mapping
                })
            });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Import thất bại');
            
            modalInstance.hide();
            showToast('Thành công', result.message || 'Đã import thành công.', 'success');
            await this.fetchData(1, '');
            resetMappingUI();
        };

        form.addEventListener('change', (event) => { if (event.target === fileInput) resetMappingUI(); });
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            console.log('Form submitted, step:', form.dataset.step);
            toggleButtonLoading(submitButton, true);
            try {
                if (form.dataset.step === 'mapping') await handleImport();
                else await handlePreview();
            } catch (err) {
                console.error('Import error:', err);
                showToast('Lỗi', err.message || 'Có lỗi xảy ra khi import', 'danger');
            } finally {
                toggleButtonLoading(submitButton, false);
            }
        });

        form.dataset.initialized = 'true';
        resetMappingUI();
    }
    
    // ------------------------------------------------------------------
    // KHỞI TẠO (Chung)
    // ------------------------------------------------------------------

    setupEventListeners() {
        // Tìm kiếm
        if (this.dom.searchInput) {
            this.dom.searchInput.addEventListener('keyup', debounce(() => {
                this.fetchData(1, this.dom.searchInput.value.trim());
            }, this.DEBOUNCE_DELAY));
        }
        if (this.dom.searchForm) {
            this.dom.searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.fetchData(1, this.dom.searchInput.value.trim());
            });
        }

        // Phân trang
        if (this.dom.paginationContainer) {
            this.dom.paginationContainer.addEventListener('click', (event) => {
                event.preventDefault();
                const target = event.target.closest('a.page-link');
                if (target) {
                    const page = parseInt(target.dataset.page, 10);
                    if (!Number.isNaN(page) && page !== this.state.currentPage) {
                        this.fetchData(page, this.state.currentQuery);
                    }
                }
            });
        }

        // Chọn
        if (this.dom.btnBulkDelete) {
            this.dom.btnBulkDelete.addEventListener('click', () => this.bulkDeleteItems());
        }
        if (this.dom.selectAllCheckbox) {
            this.dom.selectAllCheckbox.addEventListener('change', (e) => {
                const isChecked = e.target.checked;
                this.dom.tableBody.querySelectorAll(`.${this.options.checkboxClass}`).forEach(cb => {
                    cb.checked = isChecked;
                    this.toggleSelection(cb.value, isChecked);
                });
            });
        }

        // Import
        if (this.dom.importExcelBtn && this.options.importConfig) {
            this.dom.importExcelBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                this.modals.import = await loadModal(this.options.importConfig.modalUrl, 'importExcelModal');
                if (!this.modals.import) return;
                this.modals.import.show();
                this.initializeImportModal(this.modals.import);
            });
        }
    }
    
    updateURL(page, query) {
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        url.searchParams.set('q', query || '');
        window.history.replaceState({}, '', url);
    }

    getURLParams() {
        const urlParams = new URLSearchParams(window.location.search);
        return {
            page: parseInt(urlParams.get('page')) || 1,
            query: urlParams.get('q') || ''
        };
    }

    init() {
        this.setupEventListeners(); // Lớp cha gắn sự kiện chung
        
        const { page, query } = this.getURLParams();
        if (this.dom.searchInput) this.dom.searchInput.value = query;
        this.fetchData(page, query);
    }
}