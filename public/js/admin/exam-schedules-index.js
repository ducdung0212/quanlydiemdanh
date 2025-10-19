document.addEventListener('DOMContentLoaded', function () {
    const API_BASE_URL = '/api/exam_schedules';
    const ITEMS_PER_PAGE = 10;
    const DEBOUNCE_DELAY = 300;

    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    const tableBody = document.getElementById('exam-schedules-table-body');
    const paginationContainer = document.getElementById('pagination-container');
    const paginationInfo = {
        start: document.getElementById('pagination-start'),
        end: document.getElementById('pagination-end'),
        total: document.getElementById('pagination-total')
    };

    if (!tableBody || !paginationContainer) {
        return;
    }

    const apiFetchFn = window.apiFetch ? window.apiFetch.bind(window) : async (url, options = {}) => {
        const response = await fetch(url, options);
        if (!response.ok) {
            throw new Error('Request failed');
        }
        return response.json();
    };
    const escapeHtml = window.escapeHtml ? window.escapeHtml.bind(window) : (value) => {
        if (typeof value !== 'string') {
            return value ?? '';
        }
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return value.replace(/[&<>"']/g, (char) => map[char]);
    };
    const renderPaginationHTML = window.renderPaginationHTML ? window.renderPaginationHTML.bind(window) : () => '';
    const updatePaginationInfo = window.updatePaginationInfo ? window.updatePaginationInfo.bind(window) : () => {};
    const updateURL = window.updateURL ? window.updateURL.bind(window) : () => {};
    const getURLParams = window.getURLParams ? window.getURLParams.bind(window) : () => ({ page: 1, query: '' });
    const showToast = window.showToast ? window.showToast.bind(window) : () => {};
    const debounce = window.debounce ? window.debounce.bind(window) : (fn) => fn;

    let currentPage = 1;
    let currentQuery = '';
    let paginationData = null;
    let isLoading = false;

    async function fetchExamSchedules(page = 1, query = '') {
        if (isLoading) {
            return;
        }
        isLoading = true;
        tableBody.innerHTML = '<tr><td colspan="7" class="text-center">Đang tải...</td></tr>';

        try {
            const url = `${API_BASE_URL}?page=${page}&limit=${ITEMS_PER_PAGE}&q=${encodeURIComponent(query)}`;
            const result = await apiFetchFn(url);
            if (!result.success || !result.data) {
                throw new Error(result.message || 'Invalid response');
            }

            paginationData = result.data;
            currentPage = paginationData.current_page;
            currentQuery = query;
            updateURL(currentPage, currentQuery);
            render();
        } catch (error) {
            console.error('Failed to fetch exam schedules:', error);
            showToast('Lỗi', error.message || 'Không thể tải dữ liệu lịch thi', 'danger');
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Không thể tải dữ liệu.</td></tr>';
            paginationData = null;
            renderPagination();
        } finally {
            isLoading = false;
        }
    }

    function render() {
        renderTable();
        renderPagination();
    }

    function renderTable() {
        if (!paginationData || !Array.isArray(paginationData.data) || paginationData.data.length === 0) {
            const message = currentQuery ? 'Không tìm thấy lịch thi nào.' : 'Chưa có lịch thi.';
            tableBody.innerHTML = `<tr><td colspan="7" class="text-center">${message}</td></tr>`;
            updatePaginationInfo(paginationInfo, paginationData);
            return;
        }

        const { data, from } = paginationData;
        const rows = data.map((schedule, index) => {
            const rowIndex = (from || 0) + index;
            const subjectCode = escapeHtml(schedule.subject_code || '');
            const subjectName = escapeHtml(schedule.subject && schedule.subject.name ? schedule.subject.name : '');
            const examDate = escapeHtml(schedule.exam_date || '');
            const examTime = escapeHtml(schedule.exam_time || '');
            const room = escapeHtml(schedule.room || '');
            const note = escapeHtml(schedule.note || '');

            return `
                <tr>
                    <td class="text-center">${rowIndex}</td>
                    <td>${subjectCode}</td>
                    <td>${subjectName}</td>
                    <td>${examDate}</td>
                    <td>${examTime}</td>
                    <td>${room}</td>
                    <td>${note}</td>
                </tr>
            `;
        }).join('');

        tableBody.innerHTML = rows;
        updatePaginationInfo(paginationInfo, paginationData);
    }

    function renderPagination() {
        if (!paginationData) {
            paginationContainer.innerHTML = '';
            return;
        }
        paginationContainer.innerHTML = renderPaginationHTML(paginationData);
    }

    function attachEvents() {
        if (searchForm) {
            searchForm.addEventListener('submit', function (event) {
                event.preventDefault();
                const keyword = searchInput ? searchInput.value.trim() : '';
                fetchExamSchedules(1, keyword);
            });
        }

        if (searchInput) {
            const debouncedSearch = debounce(function (value) {
                fetchExamSchedules(1, value.trim());
            }, DEBOUNCE_DELAY);

            searchInput.addEventListener('input', function (event) {
                debouncedSearch(event.target.value);
            });
        }

        paginationContainer.addEventListener('click', function (event) {
            const target = event.target.closest('[data-page]');
            if (!target) {
                return;
            }
            event.preventDefault();
            const page = parseInt(target.getAttribute('data-page'), 10);
            if (!Number.isNaN(page) && page > 0 && page !== currentPage) {
                fetchExamSchedules(page, currentQuery);
            }
        });

        document.addEventListener('examSchedulesUpdated', function () {
            fetchExamSchedules(currentPage, currentQuery);
        });
    }

    function init() {
        const params = getURLParams();
        currentPage = params.page || 1;
        currentQuery = params.query || '';
        if (searchInput) {
            searchInput.value = currentQuery;
        }
        attachEvents();
        fetchExamSchedules(currentPage, currentQuery);
    }

    init();
});
