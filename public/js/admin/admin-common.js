/**
 * Admin Common JavaScript Functions
 * Shared utility functions for admin pages
 */

const AdminHelpers = {
    /**
     * Fetch API với CSRF token và error handling
     */
    apiFetch: async function (url, options = {}) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const defaultHeaders = { 'Accept': 'application/json' };
        if (csrfToken) defaultHeaders['X-CSRF-TOKEN'] = csrfToken;

        if (!(options.body instanceof FormData)) {
            defaultHeaders['Content-Type'] = 'application/json';
        }

        const config = { ...options, headers: { ...defaultHeaders, ...options.headers } };

        if (config.body && typeof config.body === 'object' && !(config.body instanceof FormData)) {
            config.body = JSON.stringify(config.body);
        }

        const response = await fetch(url, config);

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: response.statusText, errors: {} }));
            errorData.statusCode = response.status;
            throw errorData;
        }
        return response.json();
    },

    /**
     * Toggle loading state trên button
     */
    toggleButtonLoading: function (button, isLoading) {
        if (!button) return;
        const btnText = button.querySelector('.btn-text');
        const spinner = button.querySelector('.spinner-border');
        button.disabled = isLoading;
        if (btnText) btnText.classList.toggle('d-none', isLoading);
        if (spinner) spinner.classList.toggle('d-none', !isLoading);
    },

    /**
     * Xóa tất cả validation errors trong form
     */
    clearValidationErrors: function (form) {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    },

    /**
     * Hiển thị validation errors trong form
     */
    displayValidationErrors: function (form, errors) {
        this.clearValidationErrors(form);
        for (const [field, messages] of Object.entries(errors)) {
            const fieldName = field.split('.')[0];
            const input = form.querySelector(`[name="${fieldName}"]`) || form.querySelector(`[name="${fieldName}[]"]`);
            if (input) {
                input.classList.add('is-invalid');
                let feedback = input.nextElementSibling;
                if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                    feedback = input.closest('.mb-3, .mb-4')?.querySelector('.invalid-feedback');
                }
                if (feedback) {
                    feedback.textContent = messages.join(' ');
                }
            }
        }
    },

    /**
     * Hiển thị toast notification
     */
    showToast: function (title, message, type = 'info') {
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-${type} text-white">
                    <strong class="me-auto">${title}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">${message}</div>
            </div>`;
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        const toastEl = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', e => e.currentTarget.remove());
    },

    /**
     * Load modal từ server
     */
    loadModal: async function (url, modalId) {
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const html = await response.text();

            // Chỉ xóa modal cùng ID nếu đã tồn tại
            const existingModal = document.getElementById(modalId);
            if (existingModal) {
                const modalInstance = bootstrap.Modal.getInstance(existingModal);
                if (modalInstance) {
                    modalInstance.hide();
                }
                // Xóa container của modal cũ
                const container = existingModal.closest('.modal-container, div');
                if (container && container.parentElement === document.body) {
                    container.remove();
                }
            }

            const tempDiv = document.createElement('div');
            tempDiv.className = 'modal-container';
            tempDiv.innerHTML = html;
            document.body.appendChild(tempDiv);

            const modalElement = document.getElementById(modalId);
            if (!modalElement) {
                console.error(`Modal with id ${modalId} not found in loaded content.`);
                return null;
            }

            const modal = new bootstrap.Modal(modalElement);
            modalElement.addEventListener('hidden.bs.modal', () => tempDiv.remove());
            return modal;
        } catch (error) {
            console.error('Failed to load modal:', error);
            this.showToast('Lỗi', 'Không thể tải cửa sổ làm việc.', 'danger');
            return null;
        }
    },

    /**
     * Debounce function
     */
    debounce: function (func, delay) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func(...args), delay);
        };
    },

    /**
     * Escape HTML để tránh XSS
     */
    escapeHtml: function (text) {
        if (typeof text !== 'string') return text;
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    },

    /**
     * Update URL với pagination và search params
     */
    updateURL: function (page, query) {
        const url = new URL(window.location);
        if (page > 1) {
            url.searchParams.set('page', page);
        } else {
            url.searchParams.delete('page');
        }
        if (query) {
            url.searchParams.set('q', query);
        } else {
            url.searchParams.delete('q');
        }
        window.history.pushState({}, '', url);
    },

    /**
     * Get URL params
     */
    getURLParams: function () {
        const params = new URLSearchParams(window.location.search);
        return {
            page: parseInt(params.get('page')) || 1,
            query: params.get('q') || ''
        };
    },

    /**
     * Render pagination HTML
     */
    renderPaginationHTML: function (paginationData) {
        if (!paginationData || paginationData.last_page <= 1) {
            return '';
        }

        const { current_page, last_page } = paginationData;
        let html = '';

        // Previous button
        html += `<li class="page-item ${current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${current_page - 1}" ${current_page === 1 ? 'tabindex="-1"' : ''}>
                <i class="icon-chevron-left"></i>
            </a>
        </li>`;

        // Page numbers with smart truncation
        const delta = 2;
        const range = [];
        const rangeWithDots = [];

        for (let i = 1; i <= last_page; i++) {
            if (i === 1 || i === last_page || (i >= current_page - delta && i <= current_page + delta)) {
                range.push(i);
            }
        }

        let l;
        for (const i of range) {
            if (l) {
                if (i - l === 2) {
                    rangeWithDots.push(l + 1);
                } else if (i - l !== 1) {
                    rangeWithDots.push('...');
                }
            }
            rangeWithDots.push(i);
            l = i;
        }

        rangeWithDots.forEach(page => {
            if (page === '...') {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            } else {
                html += `<li class="page-item ${page === current_page ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${page}">${page}</a>
                </li>`;
            }
        });

        // Next button
        html += `<li class="page-item ${current_page === last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${current_page + 1}" ${current_page === last_page ? 'tabindex="-1"' : ''}>
                <i class="icon-chevron-right"></i>
            </a>
        </li>`;

        return html;
    },

    /**
     * Update pagination info text
     */
    updatePaginationInfo: function (paginationInfo, paginationData) {
        if (!paginationInfo || !paginationData) return;

        paginationInfo.start.textContent = paginationData.from || 0;
        paginationInfo.end.textContent = paginationData.to || 0;
        paginationInfo.total.textContent = paginationData.total || 0;
    }
};

// Expose globally
window.AdminHelpers = AdminHelpers;

// Also expose individual functions for backward compatibility
window.apiFetch = AdminHelpers.apiFetch.bind(AdminHelpers);
window.toggleButtonLoading = AdminHelpers.toggleButtonLoading.bind(AdminHelpers);
window.clearValidationErrors = AdminHelpers.clearValidationErrors.bind(AdminHelpers);
window.displayValidationErrors = AdminHelpers.displayValidationErrors.bind(AdminHelpers);
window.showToast = AdminHelpers.showToast.bind(AdminHelpers);
window.loadModal = AdminHelpers.loadModal.bind(AdminHelpers);
window.debounce = AdminHelpers.debounce.bind(AdminHelpers);
window.escapeHtml = AdminHelpers.escapeHtml.bind(AdminHelpers);
window.updateURL = AdminHelpers.updateURL.bind(AdminHelpers);
window.getURLParams = AdminHelpers.getURLParams.bind(AdminHelpers);
window.renderPaginationHTML = AdminHelpers.renderPaginationHTML.bind(AdminHelpers);
window.updatePaginationInfo = AdminHelpers.updatePaginationInfo.bind(AdminHelpers);
