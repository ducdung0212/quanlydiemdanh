(function (global) {
    const Helpers = global.AdminHelpers || {};
    const noop = () => {};

    function ensureFunction(fn, fallback) {
        return typeof fn === 'function' ? fn : fallback;
    }

    class AdminEntityControl {
        constructor(options = {}) {
            if (!options.tableBody) {
                throw new Error('AdminEntityControl requires a tableBody element.');
            }

            this.apiBaseUrl = options.apiBaseUrl;
            this.itemsPerPage = options.itemsPerPage || 10;
            this.debounceDelay = Number.isFinite(options.debounceDelay) ? options.debounceDelay : 300;

            this.dom = {
                tableBody: options.tableBody,
                paginationContainer: options.paginationContainer || null,
                paginationInfo: options.paginationInfo || null,
                selectAllCheckbox: options.selectAllCheckbox || null,
                bulkDeleteButton: options.bulkDeleteButton || null,
                selectedCountSpan: options.selectedCountSpan || null,
                searchInput: options.searchInput || null,
                searchForm: options.searchForm || null,
            };

            this.checkboxSelector = options.checkboxSelector || '.entity-checkbox';
            this.getItemKey = ensureFunction(options.getItemKey, (item) => item?.id);
            this.normalizeKey = ensureFunction(options.normalizeKey, (value) => value);
            this.renderRow = ensureFunction(options.renderRow, () => '');
            this.onTableAction = ensureFunction(options.onTableAction, noop);
            this.onLoadSuccess = ensureFunction(options.onLoadSuccess, noop);
            this.onLoadError = ensureFunction(options.onLoadError, noop);

            this.messages = {
                empty: options.emptyMessage || 'Không có dữ liệu',
                notFound: options.notFoundMessage || 'Không tìm thấy dữ liệu phù hợp',
                bulkDeleteConfirm: ensureFunction(options.bulkDeleteConfirmMessage, (count) => `Bạn có chắc chắn muốn xóa ${count} mục đã chọn?`),
                bulkDeleteSuccess: options.bulkDeleteSuccessMessage || 'Đã xóa các mục đã chọn.',
                bulkDeleteError: options.bulkDeleteErrorMessage || 'Không thể xóa hàng loạt.',
                deleteConfirm: ensureFunction(options.deleteConfirmMessage, (key) => `Bạn có chắc chắn muốn xóa mục ${key}?`),
                deleteSuccess: options.deleteSuccessMessage || 'Đã xóa thành công.',
                deleteError: options.deleteErrorMessage || 'Không thể xóa mục.',
                successTitle: options.successTitle || 'Thành công',
                errorTitle: options.errorTitle || 'Lỗi',
            };

            this.bulkDeleteEndpoint = options.bulkDeleteEndpoint || `${this.apiBaseUrl}/bulk-delete`;
            this.buildBulkDeletePayload = ensureFunction(options.buildBulkDeletePayload, (keys) => ({ ids: keys }));
            this.bulkDeleteReloadQuery = options.bulkDeleteReloadQuery !== undefined ? options.bulkDeleteReloadQuery : null;

            this.deleteEndpointBuilder = ensureFunction(options.deleteEndpoint, (key) => `${this.apiBaseUrl}/${encodeURIComponent(key)}`);

            this.state = {
                currentPage: 1,
                currentQuery: '',
                paginationData: null,
                isLoading: false,
                selectedItems: new Set(),
            };

            this.debounceFactory = ensureFunction(Helpers.debounce, (fn, delay) => {
                let timeout;
                return (...args) => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => fn(...args), delay);
                };
            });
        }

        init() {
            this.setupListeners();
            const params = ensureFunction(Helpers.getURLParams, () => ({ page: 1, query: '' }))();
            if (this.dom.searchInput) {
                this.dom.searchInput.value = params.query;
            }
            this.load(params.page, params.query);
        }

        async load(page = 1, query = '') {
            if (this.state.isLoading || !this.apiBaseUrl) {
                return this.state.paginationData;
            }

            this.state.isLoading = true;
            this.showLoading();

            const url = this.buildFetchUrl(page, query);

            try {
                const apiFetch = ensureFunction(Helpers.apiFetch, global.apiFetch || null);
                if (!apiFetch) {
                    throw new Error('apiFetch helper is not available');
                }

                const result = await apiFetch(url);
                if (!result || result.success !== true || !result.data) {
                    throw new Error('Invalid API response format');
                }

                this.state.paginationData = result.data;
                this.state.currentPage = this.state.paginationData.current_page;
                this.state.currentQuery = query;

                ensureFunction(Helpers.updateURL, noop)(this.state.currentPage, this.state.currentQuery);
                this.render();
                this.onLoadSuccess(this.state.paginationData);
                return this.state.paginationData;
            } catch (error) {
                console.error('AdminEntityControl load error:', error);
                this.state.paginationData = null;
                this.renderError();
                this.onLoadError(error);
                throw error;
            } finally {
                this.state.isLoading = false;
                this.updateSelectionUI();
                if (this.dom.searchInput) {
                    this.dom.searchInput.value = this.state.currentQuery;
                }
            }
        }

        buildFetchUrl(page, query) {
            const params = new URLSearchParams();
            params.set('page', page);
            params.set('limit', this.itemsPerPage);
            if (query) {
                params.set('q', query);
            }
            return `${this.apiBaseUrl}?${params.toString()}`;
        }

        render() {
            this.renderTable();
            this.renderPagination();
            this.updateSelectionUI();
        }

        renderTable() {
            const tbody = this.dom.tableBody;
            if (!tbody) return;

            const data = this.state.paginationData?.data || [];
            if (!Array.isArray(data) || data.length === 0) {
                const message = this.state.currentQuery ? this.messages.notFound : this.messages.empty;
                tbody.innerHTML = `<tr><td colspan="6" class="text-center">${message}</td></tr>`;
                return;
            }

            const from = this.state.paginationData.from || 0;
            const escape = ensureFunction(Helpers.escapeHtml, (value) => value);

            const rows = data.map((item, index) => {
                const key = this.normalizeKey(this.getItemKey(item));
                const isSelected = this.state.selectedItems.has(key);
                return this.renderRow({
                    item,
                    index,
                    from,
                    rowNumber: from + index,
                    isSelected,
                    escapeHtml: escape,
                });
            }).join('');

            tbody.innerHTML = rows;
        }

        renderPagination() {
            const container = this.dom.paginationContainer;
            if (!container) return;

            if (!this.state.paginationData) {
                container.innerHTML = '';
                this.updatePaginationInfo(null);
                return;
            }

            const renderPaginationHTML = ensureFunction(Helpers.renderPaginationHTML, () => '');
            container.innerHTML = renderPaginationHTML(this.state.paginationData);
            this.updatePaginationInfo(this.state.paginationData);
        }

        updatePaginationInfo(paginationData) {
            const info = this.dom.paginationInfo;
            if (!info) return;

            const startEl = info.start;
            const endEl = info.end;
            const totalEl = info.total;

            if (!paginationData) {
                if (startEl) startEl.textContent = '0';
                if (endEl) endEl.textContent = '0';
                if (totalEl) totalEl.textContent = '0';
                return;
            }

            if (Helpers.updatePaginationInfo) {
                Helpers.updatePaginationInfo(info, paginationData);
                return;
            }

            if (startEl) startEl.textContent = paginationData.from || 0;
            if (endEl) endEl.textContent = paginationData.to || 0;
            if (totalEl) totalEl.textContent = paginationData.total || 0;
        }

        showLoading() {
            if (!this.dom.tableBody) return;
            this.dom.tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Đang tải...</td></tr>';
        }

        renderError() {
            if (this.dom.tableBody) {
                this.dom.tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Lỗi khi tải dữ liệu.</td></tr>';
            }
            if (this.dom.paginationContainer) {
                this.dom.paginationContainer.innerHTML = '';
            }
            this.updatePaginationInfo(null);
        }

        setupListeners() {
            if (this.dom.searchInput) {
                const handler = this.debounceFactory(() => {
                    const value = this.dom.searchInput.value.trim();
                    this.handleSearch(value);
                }, this.debounceDelay);
                this.dom.searchInput.addEventListener('keyup', handler);
            }

            if (this.dom.searchForm) {
                this.dom.searchForm.addEventListener('submit', (event) => {
                    event.preventDefault();
                    const value = this.dom.searchInput ? this.dom.searchInput.value.trim() : '';
                    this.handleSearch(value);
                });
            }

            if (this.dom.selectAllCheckbox) {
                this.dom.selectAllCheckbox.addEventListener('change', (event) => {
                    this.toggleSelectAll(event.target.checked);
                });
            }

            if (this.dom.bulkDeleteButton) {
                this.dom.bulkDeleteButton.addEventListener('click', (event) => {
                    event.preventDefault();
                    this.bulkDelete();
                });
            }

            if (this.dom.tableBody) {
                this.dom.tableBody.addEventListener('change', (event) => {
                    const checkbox = event.target.closest(this.checkboxSelector);
                    if (checkbox) {
                        this.toggleSelection(checkbox.value, checkbox.checked);
                    }
                });

                this.dom.tableBody.addEventListener('click', (event) => {
                    const actionElement = event.target.closest('[data-action]');
                    if (!actionElement) {
                        return;
                    }

                    const action = actionElement.dataset.action;
                    if (!action) {
                        return;
                    }

                    event.preventDefault();
                    this.onTableAction({
                        action,
                        dataset: actionElement.dataset,
                        event,
                        controller: this,
                    });
                });
            }

            if (this.dom.paginationContainer) {
                this.dom.paginationContainer.addEventListener('click', (event) => {
                    const link = event.target.closest('a.page-link');
                    if (!link) {
                        return;
                    }

                    const listItem = link.closest('li');
                    if (listItem && listItem.classList.contains('disabled')) {
                        event.preventDefault();
                        return;
                    }

                    const page = parseInt(link.dataset.page, 10);
                    if (Number.isNaN(page)) {
                        return;
                    }

                    event.preventDefault();
                    if (page !== this.state.currentPage) {
                        this.load(page, this.state.currentQuery);
                    }
                });
            }
        }

        handleSearch(query) {
            this.clearSelection();
            this.load(1, query);
        }

        toggleSelection(value, isSelected) {
            const key = this.normalizeKey(value);
            if (key === undefined || key === null) {
                return;
            }

            if (isSelected) {
                this.state.selectedItems.add(key);
            } else {
                this.state.selectedItems.delete(key);
            }

            this.updateSelectionUI();
        }

        toggleSelectAll(isChecked) {
            const checkboxes = this.getCheckboxes();
            checkboxes.forEach((checkbox) => {
                checkbox.checked = isChecked;
                this.toggleSelection(checkbox.value, isChecked);
            });
        }

        updateSelectionUI() {
            const selectedCount = this.state.selectedItems.size;
            if (this.dom.selectedCountSpan) {
                this.dom.selectedCountSpan.textContent = selectedCount;
            }

            if (this.dom.bulkDeleteButton) {
                this.dom.bulkDeleteButton.classList.toggle('d-none', selectedCount === 0);
            }

            if (!this.dom.selectAllCheckbox) {
                return;
            }

            const checkboxes = this.getCheckboxes();
            if (checkboxes.length === 0) {
                this.dom.selectAllCheckbox.checked = false;
                this.dom.selectAllCheckbox.indeterminate = false;
                return;
            }

            const allSelected = checkboxes.every((checkbox) => this.state.selectedItems.has(this.normalizeKey(checkbox.value)));
            const someSelected = checkboxes.some((checkbox) => this.state.selectedItems.has(this.normalizeKey(checkbox.value)));

            this.dom.selectAllCheckbox.checked = allSelected;
            this.dom.selectAllCheckbox.indeterminate = !allSelected && someSelected;
        }

        getCheckboxes() {
            if (!this.dom.tableBody) {
                return [];
            }
            return Array.from(this.dom.tableBody.querySelectorAll(this.checkboxSelector));
        }

        getRowCount() {
            if (!this.dom.tableBody) {
                return 0;
            }
            return this.dom.tableBody.querySelectorAll('tr').length;
        }

        clearSelection() {
            this.state.selectedItems.clear();
            this.updateSelectionUI();
        }

        async deleteItem(key, overrideMessages = {}) {
            const normalizedKey = this.normalizeKey(key);
            if (normalizedKey === undefined || normalizedKey === null) {
                return false;
            }

            const confirmMessage = overrideMessages.confirmMessage || this.messages.deleteConfirm(normalizedKey);
            if (confirmMessage && !window.confirm(confirmMessage)) {
                return false;
            }

            try {
                const endpoint = overrideMessages.endpoint || this.deleteEndpointBuilder(normalizedKey);
                const apiFetch = ensureFunction(Helpers.apiFetch, global.apiFetch || null);
                if (!apiFetch) {
                    throw new Error('apiFetch helper is not available');
                }

                const result = await apiFetch(endpoint, { method: 'DELETE' });

                const successMessage = result?.message || overrideMessages.successMessage || this.messages.deleteSuccess;
                if (successMessage && Helpers.showToast) {
                    Helpers.showToast(this.messages.successTitle, successMessage, 'success');
                }

                this.state.selectedItems.delete(normalizedKey);
                const hasSingleRow = this.getRowCount() <= 1;
                const targetPage = hasSingleRow && this.state.currentPage > 1 ? this.state.currentPage - 1 : this.state.currentPage;
                await this.load(targetPage, this.state.currentQuery);
                return true;
            } catch (error) {
                console.error('Delete item failed:', error);
                const errorMessage = error?.message || overrideMessages.errorMessage || this.messages.deleteError;
                if (Helpers.showToast) {
                    Helpers.showToast(this.messages.errorTitle, errorMessage, 'danger');
                }
                throw error;
            }
        }

        async bulkDelete() {
            const count = this.state.selectedItems.size;
            if (count === 0) {
                return false;
            }

            const confirmMessage = this.messages.bulkDeleteConfirm(count);
            if (confirmMessage && !window.confirm(confirmMessage)) {
                return false;
            }

            const keys = Array.from(this.state.selectedItems);

            try {
                const payload = this.buildBulkDeletePayload(keys);
                const apiFetch = ensureFunction(Helpers.apiFetch, global.apiFetch || null);
                if (!apiFetch) {
                    throw new Error('apiFetch helper is not available');
                }

                const result = await apiFetch(this.bulkDeleteEndpoint, {
                    method: 'POST',
                    body: payload,
                });

                const successMessage = result?.message || this.messages.bulkDeleteSuccess;
                if (successMessage && Helpers.showToast) {
                    Helpers.showToast(this.messages.successTitle, successMessage, 'success');
                }

                this.clearSelection();
                const query = this.bulkDeleteReloadQuery !== null ? this.bulkDeleteReloadQuery : this.state.currentQuery;
                await this.load(1, query);
                return true;
            } catch (error) {
                console.error('Bulk delete failed:', error);
                const errorMessage = error?.message || this.messages.bulkDeleteError;
                if (Helpers.showToast) {
                    Helpers.showToast(this.messages.errorTitle, errorMessage, 'danger');
                }
                throw error;
            }
        }

        getCurrentPage() {
            return this.state.currentPage;
        }

        getCurrentQuery() {
            return this.state.currentQuery;
        }

        async reloadCurrentPage() {
            return this.load(this.state.currentPage, this.state.currentQuery);
        }

        async reloadFirstPage() {
            return this.load(1, this.state.currentQuery);
        }
    }

    global.AdminEntityControl = AdminEntityControl;
})(window);
